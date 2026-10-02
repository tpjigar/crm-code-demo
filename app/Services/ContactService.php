<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Contacts\CreateContactAction;
use App\Actions\Contacts\DeleteContactAction;
use App\Actions\Contacts\UpdateContactAction;
use App\Contracts\Services\ContactServiceInterface;
use App\DTOs\ContactData;
use App\Models\Contact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContactService implements ContactServiceInterface
{
    public function __construct(
        private readonly CreateContactAction $create,
        private readonly UpdateContactAction $update,
        private readonly DeleteContactAction $delete,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Contact>
     */
    public function paginate(?string $search = null, ?int $clientId = null, int $perPage = 15): LengthAwarePaginator
    {
        return Contact::query()
            ->with('client:id,name')
            ->when($clientId !== null, fn ($q) => $q->where('client_id', $clientId))
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(function ($q) use ($term): void {
                    $q->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('job_title', 'like', $term);
                });
            })
            ->orderByDesc('is_primary')
            ->orderBy('last_name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(ContactData $data): Contact
    {
        return $this->create->execute($data);
    }

    public function update(Contact $contact, ContactData $data): Contact
    {
        return $this->update->execute($contact, $data);
    }

    public function delete(Contact $contact): void
    {
        $this->delete->execute($contact);
    }
}
