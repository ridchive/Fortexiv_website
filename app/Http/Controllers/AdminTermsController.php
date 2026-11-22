<?php

namespace App\Http\Controllers;

use App\Models\TermsVersion;
use App\Services\TermsContentSanitizer;
use App\Services\TermsOfServiceManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminTermsController extends Controller
{
    public function __construct(
        private TermsContentSanitizer $sanitizer,
        private TermsOfServiceManager $termsManager,
    ) {}

    public function edit(Request $request): View
    {
        $publishedVersion = TermsVersion::currentPublished();
        $draftVersion = TermsVersion::query()->where('status', 'draft')->latest('id')->first();
        $oldSections = $request->session()->getOldInput('sections');

        $sections = is_array($oldSections)
            ? $this->displaySections($oldSections)
            : ($draftVersion?->sections ?? $publishedVersion->sections);

        return view('admin.terms.edit', [
            'publishedVersion' => $publishedVersion->load('publisher'),
            'draftVersion' => $draftVersion,
            'sections' => $sections,
            'versions' => TermsVersion::query()
                ->with(['creator', 'publisher'])
                ->latest('id')
                ->paginate(10),
        ]);
    }

    public function saveDraft(Request $request): RedirectResponse
    {
        $sections = $this->validatedSections($request);
        $version = $this->termsManager->saveDraft($sections, $request->user());

        return redirect()->route('admin.terms.edit')
            ->with('status', 'Draft ToS tersimpan sebagai revisi #'.$version->id.'.');
    }

    public function preview(Request $request): View
    {
        return view('marketplace.terms', [
            'termsVersion' => 'Pratinjau',
            'sections' => $this->validatedSections($request),
            'isPreview' => true,
        ]);
    }

    public function publish(Request $request): RedirectResponse
    {
        $version = $this->termsManager->publish($this->validatedSections($request), $request->user());

        return redirect()->route('admin.terms.edit')
            ->with('status', 'ToS versi '.$version->version_label.' berhasil dipublikasikan.');
    }

    public function restore(TermsVersion $version, Request $request): RedirectResponse
    {
        $draft = $this->termsManager->restore($version, $request->user());

        return redirect()->route('admin.terms.edit')
            ->with('status', 'Versi lama disalin ke draft revisi #'.$draft->id.'. Tinjau sebelum dipublikasikan.');
    }

    /**
     * @return array<int, array{key: string, title: string, content: string}>
     */
    private function validatedSections(Request $request): array
    {
        $data = $request->validate([
            'sections' => ['required', 'array', 'min:1', 'max:50'],
            'sections.*' => ['required', 'array:key,title,content,position'],
            'sections.*.key' => ['required', 'string', 'max:80', 'distinct'],
            'sections.*.title' => ['required', 'string', 'max:160'],
            'sections.*.content' => ['required', 'string', 'max:20000'],
            'sections.*.position' => ['required', 'integer', 'min:0', 'max:49', 'distinct'],
        ]);

        $sections = collect($data['sections'])
            ->sortBy('position')
            ->values()
            ->map(function (array $section, int $index): array {
                $content = $this->sanitizer->sanitize($section['content']);
                if ($content === '') {
                    throw ValidationException::withMessages([
                        'sections.'.$index.'.content' => 'Isi section tidak boleh kosong setelah format berbahaya dibersihkan.',
                    ]);
                }

                return [
                    'key' => $section['key'],
                    'title' => trim($section['title']),
                    'content' => $content,
                ];
            });

        return $sections->all();
    }

    /**
     * @param  array<int|string, mixed>  $sections
     * @return array<int, array{key: string, title: string, content: string}>
     */
    private function displaySections(array $sections): array
    {
        return collect($sections)
            ->filter(fn (mixed $section): bool => is_array($section))
            ->values()
            ->map(function (array $section, int $index): array {
                $key = $section['key'] ?? null;

                return [
                    'key' => is_string($key) && $key !== '' ? $key : (string) Str::uuid(),
                    'title' => is_string($section['title'] ?? null) ? $section['title'] : '',
                    'content' => is_string($section['content'] ?? null) ? $this->sanitizer->sanitize($section['content']) : '',
                    'position' => $index,
                ];
            })
            ->all();
    }
}
