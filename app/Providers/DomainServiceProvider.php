<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Services\ClientServiceInterface;
use App\Services\ClientService;
use Illuminate\Support\ServiceProvider;

/**
 * Binds domain service interfaces to their concrete implementations.
 *
 * Controllers type-hint the interface; Laravel resolves the concrete
 * class here. Swapping implementations (for tests, feature flags,
 * alternative storage) only needs one line changed.
 */
class DomainServiceProvider extends ServiceProvider
{
    /**
     * @return array<class-string, class-string>
     */
    private function domainBindings(): array
    {
        return [
            ClientServiceInterface::class => ClientService::class,
        ];
    }

    public function register(): void
    {
        foreach ($this->domainBindings() as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }
    }
}
