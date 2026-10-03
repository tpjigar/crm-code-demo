<?php

declare(strict_types=1);

namespace App\Http\Requests\Portal\Incidents;

use App\Enums\IncidentSeverity;
use App\Models\Incident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Portal-side incident submission: the client user may only create
 * for their own tenant, so client_id is injected server-side from
 * $user->client_id — never trusted from the payload.
 */
class CreateIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->isClient()
            && $user->client_id !== null
            && $user->can('create', Incident::class);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:10000'],
            'severity' => ['required', 'string', Rule::in(IncidentSeverity::values())],
        ];
    }
}
