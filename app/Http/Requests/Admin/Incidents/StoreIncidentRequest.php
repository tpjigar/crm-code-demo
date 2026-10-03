<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Incidents;

use App\Enums\IncidentSeverity;
use App\Models\Client;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Incident::class) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', Rule::exists(Client::class, 'id')],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:10000'],
            'severity' => ['required', 'string', Rule::in(IncidentSeverity::values())],
            'assigned_to_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
        ];
    }
}
