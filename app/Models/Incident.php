<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Scopes\ClientTenantScope;
use Carbon\CarbonImmutable;
use Database\Factories\IncidentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property int $client_id
 * @property int|null $reported_by_id
 * @property int|null $assigned_to_id
 * @property string $reference
 * @property string $title
 * @property string $description
 * @property IncidentSeverity $severity
 * @property IncidentStatus $status
 * @property CarbonImmutable|null $acknowledged_at
 * @property CarbonImmutable|null $resolved_at
 * @property CarbonImmutable|null $closed_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 */
#[Fillable([
    'client_id', 'reported_by_id', 'assigned_to_id', 'reference',
    'title', 'description', 'severity', 'status',
    'acknowledged_at', 'resolved_at', 'closed_at',
])]
#[ScopedBy(ClientTenantScope::class)]
class Incident extends Model
{
    /** @use HasFactory<IncidentFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    /** @var array<string, string> */
    protected $casts = [
        'severity' => IncidentSeverity::class,
        'status' => IncidentStatus::class,
        'acknowledged_at' => 'immutable_datetime',
        'resolved_at' => 'immutable_datetime',
        'closed_at' => 'immutable_datetime',
    ];

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function slaBreached(): bool
    {
        if ($this->acknowledged_at !== null) {
            return false;
        }

        if ($this->created_at === null) {
            return false;
        }

        return $this->created_at
            ->addHours($this->severity->ackSlaHours())
            ->isPast();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'client_id', 'assigned_to_id', 'title', 'description',
                'severity', 'status',
            ])
            ->logOnlyDirty()
            ->useLogName('incident');
    }
}
