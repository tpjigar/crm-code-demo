<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Client;

/**
 * Supplementary hooks beyond what Spatie's LogsActivity trait handles.
 *
 * Spatie already logs created/updated/deleted events with field diffs.
 * This observer is kept thin — add logic here only when the audit log
 * trait is not enough (e.g., side effects, cache busting).
 */
class ClientObserver
{
    public function deleting(Client $client): void
    {
        // Reserved for cascade cleanup once Contacts / Incidents are linked.
        // Keeping the hook now so later branches can plug in without
        // changing the model declaration.
    }
}
