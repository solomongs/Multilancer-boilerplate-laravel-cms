<?php

namespace App\Modules\Cms\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * @property int $id
 * @property string $source_path
 * @property string $destination_url
 * @property int $status_code
 * @property bool $is_enabled
 * @property int $hit_count
 */
class Redirect extends Model
{
    protected $table = 'module_cms_redirects';

    /** @var list<string> */
    protected $fillable = [
        'source_path',
        'destination_url',
        'status_code',
        'is_enabled',
        'hit_count',
        'last_hit_at',
    ];

    protected static function booted(): void
    {
        static::saving(function (Redirect $redirect): void {
            $redirect->source_path = self::normalizeSourcePath($redirect->source_path);
            $destination = trim($redirect->destination_url);
            $isInternalDestination = str_starts_with($destination, '/') && ! str_starts_with($destination, '//');
            $isExternalDestination = filter_var($destination, FILTER_VALIDATE_URL) !== false
                && in_array(parse_url($destination, PHP_URL_SCHEME), ['http', 'https'], true);

            if ($redirect->source_path === '/') {
                throw ValidationException::withMessages([
                    'source_path' => 'The homepage cannot be handled by the legacy redirect registry.',
                ]);
            }

            if (! $isInternalDestination && ! $isExternalDestination) {
                throw ValidationException::withMessages([
                    'destination_url' => 'Use a site-relative path or a complete HTTP/HTTPS URL.',
                ]);
            }

            if ($isInternalDestination && self::normalizeSourcePath($destination) === $redirect->source_path) {
                throw ValidationException::withMessages([
                    'destination_url' => 'A redirect cannot point to the same path as its source.',
                ]);
            }

            if (! in_array($redirect->status_code, [301, 302, 307, 308], true)) {
                throw ValidationException::withMessages([
                    'status_code' => 'Redirect status must be 301, 302, 307 or 308.',
                ]);
            }

            $redirect->destination_url = $destination;
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'is_enabled' => 'boolean',
            'hit_count' => 'integer',
            'last_hit_at' => 'datetime',
        ];
    }

    /** @param Builder<Redirect> $query */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public static function normalizeSourcePath(string $path): string
    {
        $path = '/'.ltrim(trim($path), '/');
        $path = Str::before($path, '?');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public static function resolvePath(string $path): ?self
    {
        return static::query()
            ->enabled()
            ->where('source_path', static::normalizeSourcePath($path))
            ->first();
    }

    public function recordHit(): void
    {
        $this->increment('hit_count');
        $this->forceFill(['last_hit_at' => now()])->saveQuietly();
    }
}
