<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Clients\CreateClientAction;
use App\Actions\Clients\DeleteClientAction;
use App\Actions\Clients\UpdateClientAction;
use App\Contracts\Services\ClientServiceInterface;
use App\DTOs\ClientData;
use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClientService implements ClientServiceInterface
{
    public function __construct(
        private readonly CreateClientAction $create,
        private readonly UpdateClientAction $update,
        private readonly DeleteClientAction $delete,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Client>
     */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Client::query()
            ->with('owner:id,name,email')
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(function ($q) use ($term): void {
                    $q->where('name', 'like', $term)
                        ->orWhere('industry', 'like', $term)
                        ->orWhere('contact_email', 'like', $term);
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(ClientData $data): Client
    {
        return $this->create->execute($data);
    }

    public function update(Client $client, ClientData $data): Client
    {
        return $this->update->execute($client, $data);
    }

    public function delete(Client $client): void
    {
        $this->delete->execute($client);
    }
}
