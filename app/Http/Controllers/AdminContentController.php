<?php

namespace App\Http\Controllers;

use App\Models\EventSetting;
use App\Models\Product;
use App\Models\Project;
use App\Models\Seller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminContentController extends Controller
{
    public function articles(): View
    {
        return view('admin.articles.index', [
            'projects' => Project::query()->withCount(['products as article_products_count'])->orderByDesc('updated_at')->paginate(20),
        ]);
    }

    public function createArticle(): View
    {
        $availableClasses = Project::query()->orderBy('grade')->orderBy('class_name')->get(['id', 'grade', 'class_name']);

        return view('admin.articles.form', [
            'project' => new Project,
            'availableClasses' => $availableClasses,
        ]);
    }

    public function storeArticle(Request $request): RedirectResponse
    {
        $data = $this->validatedArticle($request);
        $data['image'] = $request->file('image')?->store('projects', 'public');
        $data['is_published'] = $request->boolean('is_published');

        $project = Project::create($data);

        // Auto-assign products belonging to the same class/seller
        Product::whereHas('seller', function ($q) use ($project) {
            $q->where('project_id', $project->id)
              ->orWhere('store_name', $project->class_name);
        })->update(['project_id' => $project->id]);

        return redirect()->route('admin.articles.index')->with('status', 'Artikel proyek berhasil dibuat.');
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

        Product::query()->where('project_id', $project->id)->update(['project_id' => null]);
        Seller::query()->where('project_id', $project->id)->update(['project_id' => null]);

        $title = $project->title;
        $project->delete();

        return redirect()->route('admin.articles.index')->with('status', 'Artikel proyek "'.$title.'" berhasil dihapus.');
    }

    public function products(Request $request): View
    {
        $status = $request->query('status', 'pending_review');
        abort_unless(in_array($status, ['pending_review', 'approved', 'revision_requested', 'rejected', 'draft'], true), 404);

        return view('admin.products.index', [
            'products' => Product::query()->with(['seller.project', 'category', 'reviewer'])->where('review_status', $status)->latest()->paginate(20)->withQueryString(),
            'status' => $status,
            'counts' => Product::query()->selectRaw('review_status, count(*) as aggregate')->groupBy('review_status')->pluck('aggregate', 'review_status'),
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
        ]);

        if (in_array($data['decision'], ['revision_requested', 'rejected'], true) && blank($data['review_notes'] ?? null)) {
            return back()->withErrors(['review_notes' => 'Catatan wajib diisi untuk permintaan revisi atau penolakan.'])->withInput();
        }

        if ($data['decision'] === 'approved') {
            abort_if($product->stock < 1, 422, 'Produk perlu memiliki stok sebelum disetujui.');
            abort_if(! $product->category_id || ! $product->project_id, 422, 'Produk harus memiliki kategori dan terhubung ke artikel/proyek kelas sebelum disetujui.');
            abort_if(blank($data['story'] ?? $product->story), 422, 'Admin perlu melengkapi cerita produk sebelum menyetujui publikasi.');
        }

        $product->update([
            'story' => $data['story'] ?? $product->story,
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
            'impact_baseline_co2' => ['required', 'numeric', 'min:0', 'max:99999'],
            'impact_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'md_file' => ['nullable', 'file', 'mimes:txt,md,markdown', 'max:2048'],
            'products' => ['nullable', 'array'],
            'products.*.co2_reduction' => ['nullable', 'numeric', 'min:0', 'max:999'],
        ]);

        $eventSetting = EventSetting::latest('id')->first();
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
            'impact_baseline_co2' => $request->input('impact_baseline_co2'),
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

        // Update product emission factors
        if ($request->has('products') && is_array($request->input('products'))) {
            foreach ($request->input('products') as $productId => $prodData) {
                if (isset($prodData['co2_reduction'])) {
                    Product::where('id', $productId)->update([
                        'co2_reduction' => (float) $prodData['co2_reduction'],
                    ]);
                }
            }
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
