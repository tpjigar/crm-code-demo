<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Contracts\Services\IncidentServiceInterface;
use App\DTOs\IncidentData;
use App\Enums\IncidentSeverity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\Incidents\CreateIncidentRequest;
use App\Http\Resources\IncidentResource;
use App\Models\Incident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncidentController extends Controller
{
    public function __construct(
        private readonly IncidentServiceInterface $incidents,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Incident::class);

        $paginator = $this->incidents->paginate(
            search: $request->string('search')->toString() ?: null,
            status: $request->string('status')->toString() ?: null,
        );

        return Inertia::render('portal/incidents/index', [
            'incidents' => IncidentResource::collection($paginator),
            'filters' => [
                'search' => $request->string('search')->toString() ?: null,
                'status' => $request->string('status')->toString() ?: null,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Incident::class);

        return Inertia::render('portal/incidents/create', [
            'severities' => array_map(fn (IncidentSeverity $s): array => [
                'value' => $s->value,
                'label' => $s->label(),
                'ack_sla_hours' => $s->ackSlaHours(),
            ], IncidentSeverity::cases()),
        ]);
    }

    public function store(CreateIncidentRequest $request): RedirectResponse
    {
        $user = $request->user();

        // client_id injected from the authenticated user — never trusted
        // from the payload — so a client cannot create incidents for
        // another tenant even by forging the form.
        $payload = array_merge($request->validated(), [
            'client_id' => $user?->client_id,
        ]);

        $incident = $this->incidents->create(
            IncidentData::fromValidated($payload, reportedById: $user?->id),
        );

        return redirect()
            ->route('portal.incidents.show', $incident)
            ->with('success', "Incident {$incident->reference} submitted.");
    }

    public function show(Incident $incident): Response
    {
        $this->authorize('view', $incident);

        $incident->load(['assignee:id,name']);

        return Inertia::render('portal/incidents/show', [
            'incident' => new IncidentResource($incident),
        ]);
    }
}
