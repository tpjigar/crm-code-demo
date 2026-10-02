<?php

declare(strict_types=1);

namespace App\Actions\Clients;

use App\DTOs\ClientData;
use App\Models\Client;

class CreateClientAction
{
    public function execute(ClientData $data): Client
    {
        return Client::create($data->toArray());
    }
}
