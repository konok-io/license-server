<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Contracts\LicenseRepositoryInterface;
use App\Repositories\CustomerRepository;
use App\Repositories\LicenseRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        CustomerRepositoryInterface::class => CustomerRepository::class,
        LicenseRepositoryInterface::class  => LicenseRepository::class,
    ];

    public function register(): void
    {
        // Bindings handled via the $bindings property above.
    }

    public function boot(): void
    {
        //
    }
}
