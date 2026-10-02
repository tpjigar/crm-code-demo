<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Contact;
use App\Models\User;
use App\Services\Security\SensitiveDataMasker;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Contact
 */
class ContactResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewer = $request->user();
        $masker = app(SensitiveDataMasker::class);

        $shouldMask = $this->shouldMaskFor($viewer);

        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'job_title' => $this->job_title,
            'email' => $shouldMask ? $masker->email($this->email) : $this->email,
            'phone' => $shouldMask ? $masker->phone($this->phone) : $this->phone,
            'email_masked' => $shouldMask,
            'phone_masked' => $shouldMask,
            'is_primary' => $this->is_primary,
            'notes' => $this->notes,
            'client' => $this->whenLoaded('client', fn (): array => [
                'id' => $this->client?->id,
                'name' => $this->client?->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Mask for client-role viewers unless they hold the explicit
     * contacts.view_sensitive permission. Super admins always see raw.
     */
    private function shouldMaskFor(?User $viewer): bool
    {
        if (! $viewer instanceof User) {
            return true;
        }

        if ($viewer->isSuperAdmin()) {
            return false;
        }

        return ! $viewer->can('contacts.view_sensitive');
    }
}
