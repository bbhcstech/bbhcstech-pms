namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Register the RoleMiddleware globally or for specific routes
        Route::aliasMiddleware('role', \App\Http\Middleware\RoleMiddleware::class);
     
   
        
    }

    /**
     * Define the route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function map()
    {
        // Your route mappings
    }
}
