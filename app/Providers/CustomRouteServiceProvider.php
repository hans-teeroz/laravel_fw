<?php

namespace App\Providers;

// use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class CustomRouteServiceProvider extends ServiceProvider
{
    protected $namespaceApp = 'App\Http\Controllers\App';
    protected $namespaceCrm = 'App\Http\Controllers\App';
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->v1App();
        $this->v1Crm();
    }

    protected function v1App()
    {

        Route::prefix('api/app/v1')
            ->middleware('api')
            ->namespace($this->namespaceApp)
            ->group(base_path('routes/v1/api_app.php'));

    }
    protected function v1Crm()
    {

        Route::prefix('api/crm/v1')
            ->middleware('api')
            ->namespace($this->namespaceCrm)
            ->group(base_path('routes/v1/api_crm.php'));

    }
}
