<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\IncidentServiceInterface;
use App\DTOs\IncidentData;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Exceptions\InvalidIncidentTransitionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Incidents\StoreIncidentRequest;
use App\Http\Requests\Admin\Incidents\UpdateIncidentRequest;
use App\Http\Resources\IncidentResource;
use App\Models\Client;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
            clientId: $request->integer('client_id') ?: null,
        );

        return Inertia::render('admin/incidents/index', [
            'incidents' => IncidentResource::collection($paginator),
            'filters' => [
                'search' => $request->string('search')->toString() ?: null,
                'status' => $request->string('status')->toString() ?: null,
                'client_id' => $request->integer('client_id') ?: null,
            ],
            'clients' => $this->clientOptions(),
            'statuses' => $this->statusOptions(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Incident::class);

        return Inertia::render('admin/incidents/create', [
            'clients' => $this->clientOptions(),
            'assignees' => $this->assigneeOptions(),
            'severities' => $this->severityOptions(),
        ]);
    }

    public function store(StoreIncidentRequest $request): RedirectResponse
    {
        $incident = $this->incidents->create(
            IncidentData::fromValidated($request->validated(), reportedById: $request->user()?->id),
        );

        return redirect()
            ->route('admin.incidents.show', $incident)
            ->with('success', "Incident {$incident->reference} created.");
    }

    public function show(Incident $incident): Response
    {
        $this->authorize('view', $incident);

        $incident->load(['client:id,name', 'assignee:id,name', 'reporter:id,name']);

        return Inertia::render('admin/incidents/show', [
            'incident' => new IncidentResource($incident),
            'assignees' => $this->assigneeOptions(),
        ]);
    }

    public function edit(Incident $incident): Response
    {
        $this->authorize('update', $incident);

        $incident->load(['client:id,name', 'assignee:id,name']);

        return Inertia::render('admin/incidents/edit', [
            'incident' => new IncidentResource($incident),
            'clients' => $this->clientOptions(),
            'assignees' => $this->assigneeOptions(),
            'severities' => $this->severityOptions(),
        ]);
    }

    public function update(UpdateIncidentRequest $request, Incident $incident): RedirectResponse
    {
        $this->incidents->update($incident, IncidentData::fromValidated($request->validated()));

        return redirect()
            ->route('admin.incidents.show', $incident)
            ->with('success', 'Incident updated.');
    }

    public function destroy(Incident $incident): RedirectResponse
    {
        $this->authorize('delete', $incident);

        $this->incidents->delete($incident);

        return redirect()
            ->route('admin.incidents.index')
            ->with('success', 'Incident deleted.');
    }

    public function transition(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('transition', $incident);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(IncidentStatus::values())],
        ]);

        try {
            $this->incidents->transition($incident, IncidentStatus::from($validated['status']));
        } catch (InvalidIncidentTransitionException $e) {
            throw ValidationException::withMessages(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Status updated.');
    }

    public function assign(Request $request, Incident $incident): RedirectResponse
    {
        $this->authorize('assign', $incident);

        $validated = $request->validate([
            'assigned_to_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
        ]);

        $assignee = isset($validated['assigned_to_id'])
            ? User::find($validated['assigned_to_id'])
            : null;

        $this->incidents->assign($incident, $assignee);

        return back()->with('success', 'Assignee updated.');
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function clientOptions(): array
    {
        return Client::query()->orderBy('name')->get(['id', 'name'])
            ->map(fn (Client $c): array => ['id' => $c->id, 'name' => $c->name])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function assigneeOptions(): array
    {
        return User::query()->orderBy('name')->get(['id', 'name'])
            ->map(fn (User $u): array => ['id' => $u->id, 'name' => $u->name])
            ->all();
    }

    /**
     * @return array<int, array{value: string, label: string, color: string, ack_sla_hours: int}>
     */
    private function severityOptions(): array
    {
        return array_map(fn (IncidentSeverity $s): array => [
            'value' => $s->value,
            'label' => $s->label(),
            'color' => $s->badgeColor(),
            'ack_sla_hours' => $s->ackSlaHours(),
        ], IncidentSeverity::cases());
    }

    /**
     * @return array<int, array{value: string, label: string, color: string}>
     */
    private function statusOptions(): array
    {
        return array_map(fn (IncidentStatus $s): array => [
            'value' => $s->value,
            'label' => $s->label(),
            'color' => $s->badgeColor(),
        ], IncidentStatus::cases());
    }
}
