<?php

namespace App\Services;

use App\Models\TermsVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TermsOfServiceManager
{
    /**
     * @param  array<int, array{key: string, title: string, content: string}>  $sections
     */
    public function saveDraft(array $sections, User $admin): TermsVersion
    {
        return DB::transaction(function () use ($sections, $admin): TermsVersion {
            $this->lockPublishedVersion();

            return $this->createDraft($sections, $admin);
        });
    }

    /**
     * @param  array<int, array{key: string, title: string, content: string}>  $sections
     */
    public function publish(array $sections, User $admin): TermsVersion
    {
        return DB::transaction(function () use ($sections, $admin): TermsVersion {
            $this->lockPublishedVersion();
            $version = $this->createDraft($sections, $admin);

            TermsVersion::query()
                ->where('status', 'published')
                ->update(['status' => 'archived']);

            $version->update([
                'version_label' => 'tos-'.$version->id,
                'status' => 'published',
                'published_by' => $admin->id,
                'published_at' => now(),
            ]);

            return $version;
        });
    }

    public function restore(TermsVersion $version, User $admin): TermsVersion
    {
        return DB::transaction(function () use ($version, $admin): TermsVersion {
            $this->lockPublishedVersion();
            $sourceLabel = $version->version_label ?? 'Draft #'.$version->id;

            return $this->createDraft(
                $version->sections,
                $admin,
                'Memulihkan konten dari '.$sourceLabel,
            );
        });
    }

    /**
     * @param  array<int, array{key: string, title: string, content: string}>  $sections
     */
    private function createDraft(array $sections, User $admin, ?string $note = null): TermsVersion
    {
        $previousDraft = TermsVersion::query()->where('status', 'draft')->latest('id')->first();
        $comparisonVersion = $previousDraft ?? TermsVersion::currentPublished();
        [$summary, $changedSections] = $this->summarizeChanges($comparisonVersion->sections, $sections);

        if ($note) {
            $summary = $note.($summary === 'Tidak ada perubahan konten.' ? '.' : ': '.$summary);
        }

        TermsVersion::query()->where('status', 'draft')->update(['status' => 'superseded']);

        return TermsVersion::query()->create([
            'sections' => $sections,
            'status' => 'draft',
            'summary' => $summary,
            'changed_sections' => $changedSections,
            'created_by' => $admin->id,
        ]);
    }

    /**
     * @param  array<int, array{key: string, title: string, content: string}>  $before
     * @param  array<int, array{key: string, title: string, content: string}>  $after
     * @return array{string, array<int, array{section: string, change: string}>}
     */
    private function summarizeChanges(array $before, array $after): array
    {
        $beforeByKey = collect($before)->keyBy('key');
        $afterByKey = collect($after)->keyBy('key');
        $changes = [];

        foreach ($after as $section) {
            $previous = $beforeByKey->get($section['key']);

            if (! $previous) {
                $changes[] = ['section' => $section['title'], 'change' => 'ditambahkan'];

                continue;
            }

            if ($previous['title'] !== $section['title'] || $previous['content'] !== $section['content']) {
                $changes[] = ['section' => $section['title'], 'change' => 'diperbarui'];
            }
        }

        foreach ($before as $section) {
            if (! $afterByKey->has($section['key'])) {
                $changes[] = ['section' => $section['title'], 'change' => 'dihapus'];
            }
        }

        $beforeKeys = array_column($before, 'key');
        $afterKeys = array_column($after, 'key');
        $sharedBeforeKeys = array_values(array_filter($beforeKeys, fn (string $key): bool => in_array($key, $afterKeys, true)));
        $sharedAfterKeys = array_values(array_filter($afterKeys, fn (string $key): bool => in_array($key, $beforeKeys, true)));

        if ($sharedBeforeKeys !== $sharedAfterKeys) {
            $changes[] = ['section' => 'Semua section', 'change' => 'urutan diubah'];
        }

        if ($changes === []) {
            return ['Tidak ada perubahan konten.', []];
        }

        $summary = collect($changes)
            ->map(fn (array $change): string => ucfirst($change['change']).': '.$change['section'])
            ->implode('; ');

        return [$summary, $changes];
    }

    private function lockPublishedVersion(): void
    {
        TermsVersion::query()
            ->where('status', 'published')
            ->latest('id')
            ->lockForUpdate()
            ->firstOrFail();
    }
}
