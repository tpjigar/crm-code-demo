<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ClientTenantScope;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property int $client_id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $job_title
 * @property string|null $email
 * @property string|null $phone
 * @property bool $is_primary
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read string $full_name
 */
#[Fillable([
    'client_id', 'first_name', 'last_name', 'job_title',
    'email', 'phone', 'is_primary', 'notes',
])]
#[ScopedBy(ClientTenantScope::class)]
class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    /** @var array<string, string> */
    protected $casts = [
        'is_primary' => 'bool',
    ];

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'client_id', 'first_name', 'last_name', 'job_title',
                'email', 'phone', 'is_primary',
            ])
            ->logOnlyDirty()
            ->useLogName('contact');
    }
}
