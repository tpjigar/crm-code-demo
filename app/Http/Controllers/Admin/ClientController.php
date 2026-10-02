<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\ClientServiceInterface;
use App\DTOs\ClientData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Clients\StoreClientRequest;
use App\Http\Requests\Admin\Clients\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\User;
use App\Services\Clients\SecurityScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Thin CRUD controller — authorization lives in FormRequests + Policy,
 * data mutation lives in ClientService + Actions. The controller just
 * wires the HTTP boundary.
 */
class ClientController extends Controller
{
    public function __construct(
        private readonly ClientServiceInterface $clients,
        private readonly SecurityScoreService $scoring,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Client::class);

        $search = $request->string('search')->toString() ?: null;
        $paginator = $this->clients->paginate($search);

        return Inertia::render('admin/clients/index', [
            'clients' => ClientResource::collection($paginator),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Client::class);

        return Inertia::render('admin/clients/create', [
            'owners' => $this->ownerOptions(),
        ]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = $this->clients->create(ClientData::fromValidated($request->validated()));

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Client created.');
    }

    public function show(Client $client): Response
    {
        $this->authorize('view', $client);

        $client->load('owner:id,name,email');

        return Inertia::render('admin/clients/show', [
            'client' => new ClientResource($client),
            'security' => [
                'score' => $this->scoring->compute($client),
                'risk_level' => $this->scoring->riskLevel($client)->value,
                'risk_label' => $this->scoring->riskLevel($client)->label(),
                'risk_color' => $this->scoring->riskLevel($client)->badgeColor(),
            ],
        ]);
    }

    public function edit(Client $client): Response
    {
        $this->authorize('update', $client);

        $client->load('owner:id,name,email');

        return Inertia::render('admin/clients/edit', [
            'client' => new ClientResource($client),
            'owners' => $this->ownerOptions(),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->clients->update($client, ClientData::fromValidated($request->validated()));

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Client updated.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $this->clients->delete($client);

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'Client deleted.');
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function ownerOptions(): array
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user): array => ['id' => $user->id, 'name' => $user->name])
            ->all();
    }
}
