<?php

declare(strict_types=1);

namespace App\Actions\Contacts;

use App\DTOs\ContactData;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;

class CreateContactAction
{
    public function execute(ContactData $data): Contact
    {
        return DB::transaction(function () use ($data): Contact {
            if ($data->isPrimary) {
                Contact::query()
                    ->where('client_id', $data->clientId)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }

            return Contact::create($data->toArray());
        });
    }
}
