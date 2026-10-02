<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\ContactServiceInterface;
use App\DTOs\ContactData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contacts\StoreContactRequest;
use App\Http\Requests\Admin\Contacts\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Client;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function __construct(
        private readonly ContactServiceInterface $contacts,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Contact::class);

        $search = $request->string('search')->toString() ?: null;
        $clientId = $request->integer('client_id') ?: null;

        $paginator = $this->contacts->paginate($search, $clientId);

        return Inertia::render('admin/contacts/index', [
            'contacts' => ContactResource::collection($paginator),
            'filters' => [
                'search' => $search,
                'client_id' => $clientId,
            ],
            'clients' => $this->clientOptions(),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Contact::class);

        return Inertia::render('admin/contacts/create', [
            'clients' => $this->clientOptions(),
            'preselected_client_id' => $request->integer('client_id') ?: null,
        ]);
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $contact = $this->contacts->create(ContactData::fromValidated($request->validated()));

        return redirect()
            ->route('admin.contacts.show', $contact)
            ->with('success', 'Contact created.');
    }

    public function show(Contact $contact): Response
    {
        $this->authorize('view', $contact);

        $contact->load('client:id,name');

        return Inertia::render('admin/contacts/show', [
            'contact' => new ContactResource($contact),
        ]);
    }

    public function edit(Contact $contact): Response
    {
        $this->authorize('update', $contact);

        $contact->load('client:id,name');

        return Inertia::render('admin/contacts/edit', [
            'contact' => new ContactResource($contact),
            'clients' => $this->clientOptions(),
        ]);
    }

    public function update(UpdateContactRequest $request, Contact $contact): RedirectResponse
    {
        $this->contacts->update($contact, ContactData::fromValidated($request->validated()));

        return redirect()
            ->route('admin.contacts.show', $contact)
            ->with('success', 'Contact updated.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $this->authorize('delete', $contact);

        $this->contacts->delete($contact);

        return redirect()
            ->route('admin.contacts.index')
            ->with('success', 'Contact deleted.');
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function clientOptions(): array
    {
        return Client::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Client $client): array => ['id' => $client->id, 'name' => $client->name])
            ->all();
    }
}
