<?php

namespace AshAllenDesign\Coinverter\Providers;

use AshAllenDesign\Coinverter\ExchangeRatesApiAdapter;
use AshAllenDesign\Coinverter\Contracts\Coinverter;
use AshAllenDesign\Coinverter\CoinverterApiAdapter;
use Illuminate\Support\ServiceProvider;

class CoinverterServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Coinverter::class, function($app) {
            switch($app->make('config')->get('coinverter.driver')) {
                case 'currencyconverterapi' :
                    return new CoinverterApiAdapter();
                case 'exchangeratesapi' :
                    return new ExchangeRatesApiAdapter();
                default:
                    throw new \Exception('Invalid Coinverter driver.');
            }
        });

        $this->app->alias(Coinverter::class, 'coinverter');
    }

    public function boot()
    {
        $this->publishes([dirname(__DIR__,1) . '/config/coinverter.php' => config_path('coinverter.php')]);
        $this->mergeConfigFrom(dirname(__DIR__,1) . '/config/coinverter.php', 'coinverter');
    }
}
