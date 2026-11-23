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

    public function heroImageUrl(): ?string
    {
        if (! $this->hero_image) {
            return null;
        }

        if (str_starts_with($this->hero_image, 'http://') || str_starts_with($this->hero_image, 'https://')) {
            return $this->hero_image;
        }

        return route('storage.fallback', [
            'path' => ltrim($this->hero_image, '/'),
        ]);
    }

    public function impactImageUrl(): ?string
    {
        if (! $this->impact_image) {
            return null;
        }

        if (str_starts_with($this->impact_image, 'http://') || str_starts_with($this->impact_image, 'https://')) {
            return $this->impact_image;
        }

        return route('storage.fallback', [
            'path' => ltrim($this->impact_image, '/'),
        ]);
    }

    public function impactBodyAsMarkdown(): string
    {
        $defaultBody = "## Mengapa Green Ecosystem Impact Tracker?\n\nProyek FORTEXIV bukan sekadar marketplace produk sekolah biasa. Melalui tema **Green Ecosystem**, empat kelas berkolaborasi menjalankan aksi nyata untuk merawat bumi melalui kebun sekolah, pengolahan limbah daur ulang, dan produksi bernilai guna tinggi.\n\n> \"Setiap pilihan konsumsi yang kita buat memiliki jejak terhadap lingkungan. Di FORTEXIV, kami memilih konsumsi bijak demi bumi yang tetap lestari.\"\n\n### Tiga Pilar Reduksi Emisi Kami:\n- **Pemanfaatan Bahan Daur Ulang**: Mengolah limbah kertas, karton, dan kain perca menjadi kartu ucapan, tas belanja, dan tempat pensil tanpa menambang bahan baku baru.\n- **Pangan & Pertanian Organik**: Sayuran dan produk olahan dipanen langsung dari kebun kelas tanpa pestisida kimia sintetis dan bebas rantai pasok panjang.\n- **Nol Sampah Ekspedisi (Direct Pickup)**: Seluruh pesanan diambil langsung di acara tanpa kemasan bubble wrap, plastik kurir berlebih, maupun emisi bahan bakar kendaraan ekspedisi.\n\nKetika Anda memesan karya kelas di FORTEXIV, Anda secara langsung berpartisipasi menurunkan jejak karbon sekolah kita.";
        $content = $this->impact_body ?: $defaultBody;

        return preg_replace_callback(
            '/!\[([^\]]*)\]\(([^)]+)\)/',
            function ($matches) {
                $alt = $matches[1];
                $url = trim($matches[2], " \t\n\r\0\x0B\"'");

                if (preg_match('#(?:https?://[^/]+)?/?storage/(.+)$#i', $url, $pathMatches)) {
                    $storagePath = trim($pathMatches[1], " \t\n\r\0\x0B\"'");

                    return "![{$alt}](".route('storage.fallback', ['path' => $storagePath]).')';
                }

                return $matches[0];
            },
            $content
        );
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
