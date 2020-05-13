<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use TCG\Voyager\Facades\Voyager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Voyager::useModel('DataType', \App\Models\Voyager\DataType::class);
        Voyager::useModel('DataRow', \App\Models\Voyager\DataRow::class);
        Voyager::useModel('DataFilter', \App\Models\Voyager\DataFilter::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
