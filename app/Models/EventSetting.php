<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_start',
        'po_end',
        'payment_deadline',
        'event_at',
        'event_location',
        'pickup_instructions',
        'banner',
        'hero_title',
        'hero_description',
        'hero_image',
        'impact_title',
        'impact_intro',
        'impact_body',
        'impact_image',
        'impact_baseline_co2',
        'change_reason',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'po_start' => 'datetime',
            'po_end' => 'datetime',
            'payment_deadline' => 'datetime',
            'event_at' => 'datetime',
            'impact_baseline_co2' => 'decimal:2',
        ];
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): ?self
    {
        $current = static::query()->with('updater')->latest('id')->first();
        if (! $current) {
            return null;
        }

        $needsHero = is_null($current->hero_title) && is_null($current->hero_image);
        $needsImpact = is_null($current->impact_title) && is_null($current->impact_body);

        if ($needsHero || $needsImpact) {
            $fallback = static::query()
                ->where('id', '<', $current->id)
                ->where(function ($query) {
                    $query->whereNotNull('hero_title')
                        ->orWhereNotNull('hero_image')
                        ->orWhereNotNull('impact_title')
                        ->orWhereNotNull('impact_body');
                })
                ->latest('id')
                ->first();

            if ($fallback) {
                $dirty = false;
                if ($needsHero) {
                    $current->hero_title = $fallback->hero_title;
                    $current->hero_description = $fallback->hero_description;
                    $current->hero_image = $fallback->hero_image;
                    $dirty = true;
                }
                if ($needsImpact) {
                    $current->impact_title = $fallback->impact_title;
                    $current->impact_intro = $fallback->impact_intro;
                    $current->impact_body = $fallback->impact_body;
                    $current->impact_image = $fallback->impact_image;
                    $current->impact_baseline_co2 = $fallback->impact_baseline_co2;
                    $dirty = true;
                }

                if ($dirty && $current->exists) {
                    $current->save();
                }
            }
        }

        return $current;
    }
}
