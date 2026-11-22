<?php

namespace App\Http\Controllers;

use App\Models\EventSetting;
use App\Models\Product;
use App\Models\Project;
use App\Models\Seller;
use App\Services\CarbonImpactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminContentController extends Controller
{
    public function articles(): View
    {
        $query = Project::query()->withCount(['products as article_products_count'])->orderByDesc('updated_at');

        if (Schema::hasColumn('projects', 'has_article')) {
            $query->where('has_article', true);
        } else {
            $query->where('is_published', true);
        }

        return view('admin.articles.index', [
            'projects' => $query->paginate(20),
        ]);
    }

    public function createArticle(Request $request): View
    {
        $selectedClassId = $request->query('class_id');
        $selectedProject = $selectedClassId ? Project::find($selectedClassId) : new Project;

        $availableClassesQuery = Project::query();
        if (Schema::hasColumn('projects', 'has_article')) {
            $availableClassesQuery->where('has_article', false);
        }

        $availableClasses = $availableClassesQuery
            ->when($selectedClassId, fn ($q) => $q->orWhere('id', $selectedClassId))
            ->orderBy('grade')
            ->orderBy('class_name')
            ->get(['id', 'grade', 'class_name']);

        return view('admin.articles.form', [
            'project' => $selectedProject ?? new Project,
            'availableClasses' => $availableClasses,
        ]);
    }

    public function storeArticle(Request $request): RedirectResponse
    {
        $data = $this->validatedArticle($request);
        $data['is_published'] = $request->boolean('is_published');
        if (Schema::hasColumn('projects', 'has_article')) {
            $data['has_article'] = true;
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('projects', 'public');
        }

        // Cari kelas yang dipilih dari Kelola Kelas agar tidak membuat row kelas duplikat
        $project = Project::where('class_name', $data['class_name'])->first();

        if ($project) {
            $project->update($data);
        } else {
            $project = Project::create($data);
        }

        // Auto-assign products belonging to the same class/seller
        Product::whereHas('seller', function ($q) use ($project) {
            $q->where('project_id', $project->id)
                ->orWhere('store_name', $project->class_name);
        })->update(['project_id' => $project->id]);

        if ($request->has('product_ids')) {
            Product::whereIn('id', (array) $request->input('product_ids'))->update(['project_id' => $project->id]);
        }

        return redirect()->route('admin.articles.index')->with('status', 'Artikel proyek untuk kelas "'.$project->class_name.'" berhasil disimpan.');
    }

    public function editArticle(Project $project): View
    {
        $availableClasses = Project::query()->orderBy('grade')->orderBy('class_name')->get(['id', 'grade', 'class_name']);

        return view('admin.articles.form', [
            'project' => $project,
            'availableClasses' => $availableClasses,
        ]);
    }

    public function updateArticle(Request $request, Project $project): RedirectResponse
    {
        $data = $this->validatedArticle($request, $project);
        $data['is_published'] = $request->boolean('is_published');

        if ($request->boolean('remove_image')) {
            if ($project->image) {
                Storage::disk('public')->delete($project->image);
            }
            $data['image'] = null;
        } elseif ($request->hasFile('image')) {
            $newImage = $request->file('image')->store('projects', 'public');
            if ($project->image) {
                Storage::disk('public')->delete($project->image);
            }
            $data['image'] = $newImage;
        }

        $project->update($data);

        // Auto-assign products belonging to the same class/seller
        Product::whereHas('seller', function ($q) use ($project) {
            $q->where('project_id', $project->id)
                ->orWhere('store_name', $project->class_name);
        })->update(['project_id' => $project->id]);

        if ($request->has('product_ids')) {
            Product::whereIn('id', (array) $request->input('product_ids'))->update(['project_id' => $project->id]);
        }

        return redirect()->route('admin.articles.index')->with('status', 'Artikel proyek berhasil diperbarui.');
    }

    public function archiveArticle(Project $project): RedirectResponse
    {
        $project->update(['is_published' => false]);

        return back()->with('status', 'Artikel proyek "'.$project->title.'" diarsipkan dan tidak lagi tampil untuk publik.');
    }

    public function publishArticle(Project $project): RedirectResponse
    {
        $project->update(['is_published' => true]);

        return back()->with('status', 'Artikel proyek "'.$project->title.'" berhasil dipublikasikan untuk umum.');
    }

    public function destroyArticle(Project $project): RedirectResponse
    {
        if ($project->image) {
            Storage::disk('public')->delete($project->image);
        }

        $title = $project->title;

        // Hilangkan artikel dari daftar tanpa menghapus baris kelas & akun seller
        $resetData = [
            'is_published' => false,
            'image' => null,
            'title' => 'Kelas '.$project->class_name,
            'introduction' => 'Kelas '.$project->class_name,
            'article_body' => null,
        ];
        if (Schema::hasColumn('projects', 'has_article')) {
            $resetData['has_article'] = false;
        }
        $project->update($resetData);

        return redirect()->route('admin.articles.index')->with('status', 'Artikel proyek "'.$title.'" berhasil dihapus.');
    }

    public function uploadArticleImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        $path = $request->file('image')->store('articles/content', 'public');
        $url = asset('storage/'.$path);

        return response()->json([
            'url' => $url,
            'name' => $request->file('image')->getClientOriginalName(),
        ]);
    }

    public function products(Request $request): View
    {
        $status = $request->query('status', 'pending_review');
        abort_unless(in_array($status, ['pending_review', 'approved', 'revision_requested', 'rejected', 'draft', 'live'], true), 404);

        $products = Product::query()->with(['seller.project', 'category', 'reviewer']);
        if ($status === 'live') {
            $products->where('status', 'published')
                ->where('review_status', 'approved')
                ->where('stock', '>', 0);
        } else {
            $products->where('review_status', $status);
        }

        $counts = Product::query()->selectRaw('review_status, count(*) as aggregate')->groupBy('review_status')->pluck('aggregate', 'review_status');
        $counts->put('live', Product::query()
            ->where('status', 'published')
            ->where('review_status', 'approved')
            ->where('stock', '>', 0)
            ->count());

        return view('admin.products.index', [
            'products' => $products->latest()->paginate(20)->withQueryString(),
            'status' => $status,
            'counts' => $counts,
        ]);
    }

    public function reviewProduct(Product $product): View
    {
        $product->load(['seller.project', 'category', 'project', 'reviewer']);

        return view('admin.products.review', compact('product'));
    }

    public function decideProduct(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approved,revision_requested,rejected'],
            'review_notes' => ['nullable', 'string', 'max:2000'],
            'story' => ['nullable', 'string', 'max:10000'],
            'carbon_reduction_value' => ['nullable', 'numeric', 'min:0', 'max:999999999999.999999'],
            'carbon_baseline_emission_factor' => ['nullable', 'numeric', 'min:0', 'max:999999999999.999999', 'required_with:carbon_footprint_value'],
            'carbon_footprint_value' => ['nullable', 'numeric', 'min:0', 'max:999999999999.999999', 'required_with:carbon_baseline_emission_factor'],
            'carbon_baseline_source' => ['nullable', 'string', 'max:2000', 'required_with:carbon_baseline_emission_factor'],
            'carbon_product_source' => ['nullable', 'string', 'max:2000', 'required_with:carbon_footprint_value'],
            'carbon_functional_unit' => ['nullable', 'string', 'max:120', 'required_with:carbon_baseline_emission_factor,carbon_footprint_value'],
        ]);

        if (in_array($data['decision'], ['revision_requested', 'rejected'], true) && blank($data['review_notes'] ?? null)) {
            return back()->withErrors(['review_notes' => 'Catatan wajib diisi untuk permintaan revisi atau penolakan.'])->withInput();
        }

        if ($data['decision'] === 'approved') {
            abort_if($product->stock < 1, 422, 'Produk perlu memiliki stok sebelum disetujui.');
            abort_if(! $product->category_id || ! $product->project_id, 422, 'Produk harus memiliki kategori dan terhubung ke artikel/proyek kelas sebelum disetujui.');
            abort_if(blank($data['story'] ?? $product->story), 422, 'Admin perlu melengkapi cerita produk sebelum menyetujui publikasi.');
        }

        $carbonReduction = $data['carbon_reduction_value'] ?? null;

        $product->update([
            'story' => $data['story'] ?? $product->story,
            'carbon_reduction_value' => $carbonReduction,
            'carbon_footprint_value' => $carbonReduction !== null ? 0 : ($data['carbon_footprint_value'] ?? null),
            'carbon_baseline_emission_factor' => $carbonReduction !== null ? $carbonReduction : ($data['carbon_baseline_emission_factor'] ?? null),
            'carbon_footprint_unit' => filled($carbonReduction) || filled($data['carbon_footprint_value'] ?? null)
                ? CarbonImpactService::UNIT
                : null,
            'carbon_baseline_source' => $carbonReduction !== null ? 'Nilai pengurangan karbon langsung per produk' : ($data['carbon_baseline_source'] ?? null),
            'carbon_product_source' => $carbonReduction !== null ? 'Nilai pengurangan karbon langsung per produk' : ($data['carbon_product_source'] ?? null),
            'carbon_functional_unit' => $carbonReduction !== null ? '1 produk' : ($data['carbon_functional_unit'] ?? null),
            'review_status' => $data['decision'],
            'review_notes' => $data['review_notes'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'status' => $data['decision'] === 'approved' ? 'published' : 'draft',
        ]);

        $messages = [
            'approved' => 'Produk disetujui dan sekarang dapat tampil di marketplace.',
            'revision_requested' => 'Revisi diminta. Produk tidak tampil di marketplace sampai diajukan dan disetujui kembali.',
            'rejected' => 'Produk ditolak dan tidak tampil di marketplace.',
        ];

        return redirect()->route('admin.products.index', ['status' => 'pending_review'])->with('status', $messages[$data['decision']]);
    }

    public function updateImpact(Request $request): RedirectResponse
    {
        $request->validate([
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_description' => ['nullable', 'string', 'max:2000'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'impact_title' => ['required', 'string', 'max:255'],
            'impact_intro' => ['required', 'string', 'max:2000'],
            'impact_body' => ['nullable', 'string', 'max:50000'],
            'impact_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'md_file' => ['nullable', 'file', 'mimes:txt,md,markdown', 'max:2048'],
        ]);

        $eventSetting = EventSetting::current();
        if (! $eventSetting) {
            $eventSetting = new EventSetting;
        }

        $impactBody = $request->input('impact_body');
        if ($request->hasFile('md_file')) {
            $impactBody = $request->file('md_file')->get();
        }

        $data = [
            'hero_title' => $request->input('hero_title'),
            'hero_description' => $request->input('hero_description'),
            'impact_title' => $request->input('impact_title'),
            'impact_intro' => $request->input('impact_intro'),
            'impact_body' => $impactBody,
            'updated_by' => $request->user()->id,
        ];

        if ($request->boolean('remove_hero_image')) {
            if ($eventSetting->hero_image) {
                Storage::disk('public')->delete($eventSetting->hero_image);
            }
            $data['hero_image'] = null;
        } elseif ($request->hasFile('hero_image')) {
            $newHeroImage = $request->file('hero_image')->store('hero', 'public');
            if ($eventSetting->hero_image) {
                Storage::disk('public')->delete($eventSetting->hero_image);
            }
            $data['hero_image'] = $newHeroImage;
        }

        if ($request->boolean('remove_image')) {
            if ($eventSetting->impact_image) {
                Storage::disk('public')->delete($eventSetting->impact_image);
            }
            $data['impact_image'] = null;
        } elseif ($request->hasFile('impact_image')) {
            $newImage = $request->file('impact_image')->store('impact', 'public');
            if ($eventSetting->impact_image) {
                Storage::disk('public')->delete($eventSetting->impact_image);
            }
            $data['impact_image'] = $newImage;
        }

        if ($eventSetting->exists) {
            $eventSetting->update($data);
        } else {
            $eventSetting->fill($data)->save();
        }

        return back()->with('status', 'Pengaturan Hero Beranda dan Artikel GEIT berhasil diperbarui!');
    }

    private function validatedArticle(Request $request, ?Project $currentProject = null): array
    {
        if ($request->hasFile('md_file')) {
            $request->merge(['article_body' => $request->file('md_file')->get()]);
        }

        $data = $request->validate([
            'class_name' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:160'],
            'introduction' => ['required', 'string', 'max:2000'],
            'article_body' => ['required', 'string', 'max:50000'],
            'md_file' => ['nullable', 'file', 'mimes:txt,md,markdown', 'max:2048'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        unset($data['md_file']);

        // Auto-assign grade from class or fallback
        $targetClass = Project::where('class_name', $data['class_name'])->first();
        $data['grade'] = $targetClass?->grade ?? $currentProject?->grade ?? 1;

        return $data;
    }
}
