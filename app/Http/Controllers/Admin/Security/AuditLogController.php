<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Services\Security\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function __construct(
        private readonly AuditLogService $audit,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isSuperAdmin() || ! $user->can('audit.view')) {
            throw new AuthorizationException;
        }

        $filters = [
            'log_name' => $request->string('log_name')->toString() ?: null,
            'causer_id' => $request->integer('causer_id') ?: null,
            'event' => $request->string('event')->toString() ?: null,
            'from' => $request->string('from')->toString() ?: null,
            'to' => $request->string('to')->toString() ?: null,
            'search' => $request->string('search')->toString() ?: null,
        ];

        $paginator = $this->audit->paginate(array_filter($filters, fn ($v) => $v !== null));

        $paginator->getCollection()->transform(fn (Activity $a): array => [
            'id' => $a->id,
            'log_name' => $a->log_name,
            'event' => $a->event,
            'description' => $a->description,
            'subject_type' => $a->subject_type ? class_basename($a->subject_type) : null,
            'subject_id' => $a->subject_id,
            'causer' => $a->causer ? [
                'id' => $a->causer->getKey(),
                'name' => $a->causer->getAttribute('name'),
                'email' => $a->causer->getAttribute('email'),
            ] : null,
            'properties' => $a->properties->toArray(),
            'created_at' => $a->created_at?->toIso8601String(),
        ]);

        return Inertia::render('admin/security/audit/index', [
            'logs' => $paginator,
            'filters' => $filters,
            'log_names' => $this->audit->knownLogNames(),
        ]);
    }
}
