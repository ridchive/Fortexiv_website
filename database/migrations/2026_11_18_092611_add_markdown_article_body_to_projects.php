<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->longText('article_body')->nullable();
            $table->text('story')->nullable()->change();
            $table->text('outcome')->nullable()->change();
        });

        DB::table('projects')
            ->select(['id', 'story', 'goal', 'process', 'outcome'])
            ->orderBy('id')
            ->chunkById(100, function ($projects): void {
                foreach ($projects as $project) {
                    DB::table('projects')
                        ->where('id', $project->id)
                        ->update(['article_body' => $this->legacyArticleBody($project)]);
                }
            });
    }

    public function down(): void
    {
        DB::table('projects')
            ->whereNotNull('article_body')
            ->orderBy('id')
            ->chunkById(100, function ($projects): void {
                foreach ($projects as $project) {
                    DB::table('projects')
                        ->where('id', $project->id)
                        ->update([
                            'story' => $project->article_body,
                            'goal' => null,
                            'process' => null,
                            'outcome' => '',
                        ]);
                }
            });

        DB::table('projects')->whereNull('story')->update(['story' => '']);
        DB::table('projects')->whereNull('outcome')->update(['outcome' => '']);

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('article_body');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->text('story')->nullable(false)->change();
            $table->text('outcome')->nullable(false)->change();
        });
    }

    private function legacyArticleBody(object $project): string
    {
        $sections = [];

        foreach ([
            'Cerita / latar belakang' => $project->story,
            'Tujuan proyek' => $project->goal,
        ] as $heading => $content) {
            $content = trim((string) $content);

            if ($content !== '') {
                $sections[] = "## {$heading}\n\n{$content}";
            }
        }

        $processSteps = json_decode((string) $project->process, true);
        $formattedSteps = [];

        if (is_array($processSteps)) {
            foreach ($processSteps as $step) {
                if (is_string($step) && trim($step) !== '') {
                    $formattedSteps[] = '- '.trim($step);
                }
            }
        }

        if ($formattedSteps !== []) {
            $sections[] = "## Proses\n\n".implode("\n", $formattedSteps);
        }

        $outcome = trim((string) $project->outcome);
        if ($outcome !== '') {
            $sections[] = "## Hasil dan target\n\n{$outcome}";
        }

        return implode("\n\n", $sections);
    }
};
