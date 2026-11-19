<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['process' => 'array'];
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset('storage/'.ltrim($this->image, '/'));
    }

    public function contentAsMarkdown(): string
    {
        if (filled($this->article_body)) {
            $content = $this->article_body;
        } else {
            $sections = [];

            foreach ([
                'Cerita / latar belakang' => $this->story,
                'Tujuan proyek' => $this->goal,
            ] as $heading => $sectionContent) {
                $sectionContent = trim((string) $sectionContent);

                if ($sectionContent !== '') {
                    $sections[] = "## {$heading}\n\n{$sectionContent}";
                }
            }

            $processSteps = [];
            foreach ($this->process ?? [] as $step) {
                if (is_string($step) && trim($step) !== '') {
                    $processSteps[] = '- '.trim($step);
                }
            }

            if ($processSteps !== []) {
                $sections[] = "## Proses\n\n".implode("\n", $processSteps);
            }

            $outcome = trim((string) $this->outcome);
            if ($outcome !== '') {
                $sections[] = "## Hasil dan target\n\n{$outcome}";
            }

            $content = implode("\n\n", $sections);
        }

        return preg_replace_callback(
            '/!\[([^\]]*)\]\(([^)]+)\)/',
            function ($matches) {
                $alt = $matches[1];
                $url = trim($matches[2], " \t\n\r\0\x0B\"'");

                if (preg_match('#(?:https?://[^/]+)?/?storage/(.+)$#i', $url, $pathMatches)) {
                    $storagePath = trim($pathMatches[1], " \t\n\r\0\x0B\"'");

                    return '!['.$alt.']('.asset('storage/'.$storagePath).')';
                }

                return $matches[0];
            },
            $content
        );
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function sellers(): HasMany
    {
        return $this->hasMany(Seller::class);
    }

    public function seller(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Seller::class)->latestOfMany();
    }
}
