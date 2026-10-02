<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\DTOs\ClientData;
use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ClientServiceInterface
{
    /**
     * Paginated, optionally searched list of clients.
     *
     * @return LengthAwarePaginator<int, Client>
     */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator;

    public function create(ClientData $data): Client;

    public function update(Client $client, ClientData $data): Client;

    public function delete(Client $client): void;
}
