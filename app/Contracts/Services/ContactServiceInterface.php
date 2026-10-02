<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\DTOs\ContactData;
use App\Models\Contact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ContactServiceInterface
{
    /**
     * @return LengthAwarePaginator<int, Contact>
     */
    public function paginate(?string $search = null, ?int $clientId = null, int $perPage = 15): LengthAwarePaginator;

    public function create(ContactData $data): Contact;

    public function update(Contact $contact, ContactData $data): Contact;

    public function delete(Contact $contact): void;
}
