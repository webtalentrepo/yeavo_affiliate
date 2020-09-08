<?php

namespace App\Providers;

use App\Http\Repositories\ConfigsRepository;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\Lib\V5\GoogleAdsClientBuilder;
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

        // Binds the Google Ads API client.
        $this->app->singleton('Google\Ads\GoogleAds\Lib\V5\GoogleAdsClient', function () {
            // Constructs a Google Ads API client configured from the properties file.
            return (new GoogleAdsClientBuilder())
                ->fromFile(config('app.google_ads_php_path'))
                ->withOAuth2Credential((new OAuth2TokenBuilder())
                    ->fromFile(config('app.google_ads_php_path'))
                    ->build())
                ->build();
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
