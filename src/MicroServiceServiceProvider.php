<?php

namespace Solutionplus\MicroService;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Solutionplus\MicroService\Http\Middleware\MicroServiceMiddleware;
use Solutionplus\MicroService\Console\Commands\MicroServiceInstallCommand;
use Solutionplus\MicroService\Http\Middleware\MicroServiceEstablishConnectionMiddleware;

class MicroServiceServiceProvider extends ServiceProvider
{
    private array $packageMigrations = [
        'create_micro_service_maps_table',
    ];

    private string $migrationSubFolder;

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
        require_once __DIR__ . '/Helpers/MicroServiceHelperFunctions.php';

        $this->registerRoutes();
        $this->migrationSubFolder = config('microservice.migration_sub_folder') != '' ? config('microservice.migration_sub_folder') : 'microservice/';

        if ($this->app->runningInConsole()) {

            $this->commands([
                MicroServiceInstallCommand::class,
            ]);

            /**
             * Migrations
             */
            $migrationFiles = $this->migrationFiles();
            if (\count($migrationFiles) > 0) {
                foreach ($migrationFiles as $packagePath => $migrationPath) {
                    $this->publishes([
                        $packagePath => $migrationPath,
                    ], 'micro_service_migrations');
                }
            }

            /**
             * Config and static translations
             */
            $this->publishes([
                __DIR__ . '/config/microservice.php' => config_path('microservice.php'),
            ]);

            $this->app->make(Router::class)
                ->aliasMiddleware('micro-service', MicroServiceMiddleware::class)
                ->aliasMiddleware('micro-service-establish-connection', MicroServiceEstablishConnectionMiddleware::class);
        }
    }

    protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/routes/micro_service_routes.php');
        });
    }

    protected function routeConfiguration()
    {
        return [
            'namespace' => 'Solutionplus\MicroService\Http\Controllers',
        ];
    }

    protected function migrationFiles()
    {
        $migrationFiles = [];
        foreach ($this->packageMigrations as $migrationName) {
            if (! $this->migrationExists($migrationName)) {
                $migrationFiles[__DIR__ . "/database/migrations/{$migrationName}.php.stub"] = database_path("migrations/{$this->migrationSubFolder}" . date('Y_m_d_His', time()) . "_{$migrationName}.php");
            }
        }
        return $migrationFiles;
    }

    protected function migrationExists($migrationName)
    {
        $path = database_path("migrations/{$this->migrationSubFolder}");
        if (! \is_dir($path)) \mkdir($path, 0777, true);

        $files = scandir($path);
        $pos = false;
        foreach ($files as &$value) {
            $pos = strpos($value, $migrationName);
            if ($pos !== false) return true;
        }
        return false;
    }
}
