<?php

declare(strict_types=1);

namespace App\Services\Security;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;

/**
 * Query layer over Spatie ActivityLog so the admin audit viewer stays
 * read-only. Writes come from model observers / LogsActivity on the
 * domain models (Client, Contact, Incident).
 */
class AuditLogService
{
    /**
     * @param  array<string, mixed>  $filters
     *                                         Supports: log_name (string), causer_id (int),
     *                                         event (string: created|updated|deleted),
     *                                         from (Y-m-d), to (Y-m-d), search (text in description/subject_type).
     * @return LengthAwarePaginator<int, Activity>
     */
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        /** @var Builder<Activity> $query */
        $query = Activity::query()->with(['causer:id,name,email']);

        if (! empty($filters['log_name'])) {
            $query->where('log_name', $filters['log_name']);
        }

        if (! empty($filters['causer_id'])) {
            $query->where('causer_id', (int) $filters['causer_id']);
        }

        if (! empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($term): void {
                $q->where('description', 'like', $term)
                    ->orWhere('subject_type', 'like', $term);
            });
        }

        return $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return list<string>
     */
    public function knownLogNames(): array
    {
        /** @var list<string> $names */
        $names = Activity::query()
            ->whereNotNull('log_name')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name')
            ->all();

        return $names;
    }
}
