<?php

namespace App\Modules\Leads\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

/**
 * @property int $id
 * @property int|null $assigned_to
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $company
 * @property string|null $subject
 * @property string $message
 * @property string $source
 * @property string $status
 * @property array<string, mixed>|null $metadata
 */
class Lead extends Model
{
    use SoftDeletes;

    protected $table = 'module_leads';

    /** @var list<string> */
    protected $fillable = [
        'assigned_to',
        'name',
        'email',
        'phone',
        'company',
        'subject',
        'message',
        'source',
        'status',
        'page_url',
        'ip_hash',
        'user_agent',
        'metadata',
        'internal_notes',
        'contacted_at',
    ];

    protected static function booted(): void
    {
        static::saving(function (Lead $lead): void {
            $statuses = array_keys(config('leads.statuses', []));
            $sources = array_keys(config('leads.sources', []));

            if ($statuses !== [] && ! in_array($lead->status, $statuses, true)) {
                throw ValidationException::withMessages([
                    'status' => 'The selected lead status is invalid.',
                ]);
            }

            if ($sources !== [] && ! in_array($lead->source, $sources, true)) {
                throw ValidationException::withMessages([
                    'source' => 'The selected lead source is invalid.',
                ]);
            }

            $contactedStatuses = ['contacted', 'qualified', 'proposal', 'won', 'lost'];

            if ($lead->isDirty('status')
                && in_array($lead->status, $contactedStatuses, true)
                && $lead->contacted_at === null) {
                $lead->contacted_at = now();
            }
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'contacted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @param Builder<Lead> $query */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['won', 'lost', 'spam']);
    }
}
