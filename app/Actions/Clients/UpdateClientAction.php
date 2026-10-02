<?php

declare(strict_types=1);

namespace App\Actions\Clients;

use App\DTOs\ClientData;
use App\Models\Client;

class UpdateClientAction
{
    public function execute(Client $client, ClientData $data): Client
    {
        $client->fill($data->toArray());
        $client->save();

        return $client->refresh();
    }
}
