<?php

namespace App\Providers;

use App\Http\Repositories\ConfigsRepository;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        Schema::defaultStringLength(191);

        $this->app->singleton('MyConfig', function ($app) {
            return new ConfigsRepository();
        });

        require_once __DIR__ . '/../Http/Helpers/Helper.php';
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        JsonResource::withoutWrapping();
    }
}
