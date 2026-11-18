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

    public function contentAsMarkdown(): string
    {
        if (filled($this->article_body)) {
            return $this->article_body;
        }

        $sections = [];

        foreach ([
            'Cerita / latar belakang' => $this->story,
            'Tujuan proyek' => $this->goal,
        ] as $heading => $content) {
            $content = trim((string) $content);

            if ($content !== '') {
                $sections[] = "## {$heading}\n\n{$content}";
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

        return implode("\n\n", $sections);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function sellers(): HasMany
    {
        return $this->hasMany(Seller::class);
    }
}
