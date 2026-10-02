<?php

declare(strict_types=1);

namespace App\Actions\Contacts;

use App\DTOs\ContactData;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;

class UpdateContactAction
{
    public function execute(Contact $contact, ContactData $data): Contact
    {
        return DB::transaction(function () use ($contact, $data): Contact {
            if ($data->isPrimary && ! $contact->is_primary) {
                Contact::query()
                    ->where('client_id', $data->clientId)
                    ->where('id', '!=', $contact->id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }

            $contact->fill($data->toArray())->save();

            return $contact->refresh();
        });
    }
}
