<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Project;
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
        return view('admin.articles.form', [
            'project' => new Project,
            'products' => Product::query()->with('seller.project')->orderBy('name')->get(),
        ]);
    }

    public function storeArticle(Request $request): RedirectResponse
    {
        $data = $this->validatedArticle($request);
        $productIds = $data['product_ids'] ?? [];
        unset($data['product_ids'], $data['process_steps']);
        $data['process'] = $this->processSteps($request->input('process_steps'));
        $data['image'] = $request->file('image')?->store('projects', 'public');
        $data['is_published'] = $request->boolean('is_published');

        $project = Project::create($data);
        Product::query()->whereIn('id', $productIds)->update(['project_id' => $project->id]);

        return redirect()->route('admin.articles.index')->with('status', 'Artikel proyek berhasil dibuat.');
    }

    public function editArticle(Project $project): View
    {
        return view('admin.articles.form', [
            'project' => $project,
            'products' => Product::query()->with('seller.project')->orderBy('name')->get(),
        ]);
    }

    public function updateArticle(Request $request, Project $project): RedirectResponse
    {
        $data = $this->validatedArticle($request);
        $productIds = $data['product_ids'] ?? [];
        unset($data['product_ids'], $data['process_steps']);
        $data['process'] = $this->processSteps($request->input('process_steps'));
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
        Product::query()->where('project_id', $project->id)->update(['project_id' => null]);
        Product::query()->whereIn('id', $productIds)->update(['project_id' => $project->id]);

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
            'impact_title' => ['required', 'string', 'max:255'],
            'impact_intro' => ['required', 'string', 'max:2000'],
            'impact_body' => ['nullable', 'string', 'max:50000'],
            'impact_baseline_co2' => ['required', 'numeric', 'min:0', 'max:99999'],
            'impact_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'md_file' => ['nullable', 'file', 'mimes:txt,md,markdown', 'max:2048'],
            'products' => ['nullable', 'array'],
            'products.*.co2_reduction' => ['nullable', 'numeric', 'min:0', 'max:999'],
        ]);

        $eventSetting = \App\Models\EventSetting::latest('id')->first();
        if (! $eventSetting) {
            $eventSetting = new \App\Models\EventSetting();
        }

        $impactBody = $request->input('impact_body');
        if ($request->hasFile('md_file')) {
            $impactBody = $request->file('md_file')->get();
        }

        $data = [
            'impact_title' => $request->input('impact_title'),
            'impact_intro' => $request->input('impact_intro'),
            'impact_body' => $impactBody,
            'impact_baseline_co2' => $request->input('impact_baseline_co2'),
            'updated_by' => $request->user()->id,
        ];

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

        return back()->with('status', 'Artikel GEIT, foto header & background Hero, dan parameter reduksi emisi berhasil diperbarui!');
    }

    private function validatedArticle(Request $request): array
    {
        return $request->validate([
            'class_name' => ['required', 'string', 'max:100'],
            'grade' => ['required', 'integer', 'min:1', 'max:12'],
            'title' => ['required', 'string', 'max:160'],
            'introduction' => ['required', 'string', 'max:2000'],
            'story' => ['required', 'string', 'max:10000'],
            'goal' => ['nullable', 'string', 'max:5000'],
            'process_steps' => ['nullable', 'string', 'max:10000'],
            'outcome' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'product_ids' => [$request->boolean('is_published') ? 'required' : 'nullable', 'array', 'min:1'],
            'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
        ]);
    }

    private function processSteps(?string $steps): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $steps ?? ''))
            ->map(fn (string $step): string => trim($step))
            ->filter()
            ->values()
            ->all();
    }
}
