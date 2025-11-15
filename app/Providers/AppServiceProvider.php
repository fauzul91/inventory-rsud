<?php

namespace App\Providers;

use App\Interfaces\V1\AccountRepositoryInterface;
use App\Interfaces\V1\BastRepositoryInterface;
use App\Interfaces\V1\CategoryRepositoryInterface;
use App\Interfaces\V1\JabatanRepositoryInterface;
use App\Interfaces\V1\PenerimaanRepositoryInterface;
use App\Interfaces\V1\SatuanRepositoryInterface;
use App\Repositories\V1\AccountRepository;
use App\Repositories\V1\BastRepository;
use App\Repositories\V1\CategoryRepository;
use App\Repositories\V1\JabatanRepository;
use App\Repositories\V1\PenerimaanRepository;
use App\Repositories\V1\SatuanRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(SatuanRepositoryInterface::class, SatuanRepository::class);
        $this->app->bind(JabatanRepositoryInterface::class, JabatanRepository::class);
        $this->app->bind(PenerimaanRepositoryInterface::class, PenerimaanRepository::class);
        $this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);
        $this->app->bind(BastRepositoryInterface::class, BastRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
