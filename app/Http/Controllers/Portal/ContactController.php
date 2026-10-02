<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Contracts\Services\ContactServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Client-portal read-only view of contacts.
 *
 * Thanks to Contact's ClientTenantScope the paginator will *only*
 * return rows for the authenticated client's tenant — the controller
 * does not add a where clause, which prevents a developer from
 * forgetting it and leaking data across tenants.
 *
 * The policy's view() method re-checks client_id on show() to defend
 * against route-ID guessing attacks that could bypass the scope on a
 * direct find.
 */
class ContactController extends Controller
{
    public function __construct(
        private readonly ContactServiceInterface $contacts,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Contact::class);

        $search = $request->string('search')->toString() ?: null;
        $paginator = $this->contacts->paginate($search);

        return Inertia::render('portal/contacts/index', [
            'contacts' => ContactResource::collection($paginator),
            'filters' => ['search' => $search],
        ]);
    }

    public function show(Contact $contact): Response
    {
        $this->authorize('view', $contact);

        return Inertia::render('portal/contacts/show', [
            'contact' => new ContactResource($contact),
        ]);
    }
}
