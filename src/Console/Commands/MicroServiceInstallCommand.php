<?php

namespace Solutionplus\MicroService\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Database\Seeders\MicroServiceMapsTableSeeder;

class MicroServiceInstallCommand extends Command
{
    protected $envFile;

    protected $envContent;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ms:install';

    protected $envKeys;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and Publish MicroService Package';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->envFile = App::environmentFilePath();
        $this->envContent = \file_get_contents($this->envFile);
        $this->envContent .= "\n";
        $this->envKeys = [
            'MS_SECURE_REQUESTS_ONLY' => $this->appendToEnvContent('MS_SECURE_REQUESTS_ONLY', 'true'),
            'MS_GLOBAL_PROJECT_SECRET' => $this->appendToEnvContent('MS_GLOBAL_PROJECT_SECRET', ''),
            'MS_LOCAL_SECRET' => $this->appendToEnvContent('MS_LOCAL_SECRET', '"' . generate_local_secret(16) . '"'),
            'MS_DISABLE_PACKAGE_MIDDLEWARE' => $this->appendToEnvContent('MS_DISABLE_PACKAGE_MIDDLEWARE', 'true'),
        ];
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Publishing configuration...');

        $this->publishConfiguration();

        $this->info('Adding package .env keys');
        \file_put_contents($this->envFile, $this->envContent);
        $this->info("The keys[" . \implode(', ', \array_keys($this->envKeys)). "] has added to project .env file");

        $this->info('Caching configs...');
        $this->call('config:cache');

        $this->info('Running migrate command...');

        $this->runMigration();

        return Command::SUCCESS;
    }

    private function appendToEnvContent(string $envKey, string $envKeyValue = ''): void
    {
        $this->envContent = append_to_env_content(
            envContent: $this->envContent, 
            envKey: $envKey, 
            envKeyValue: $envKeyValue
        );
    }

    private function publishConfiguration()
    {
        $finishingMessage = 'MicroService configuration file is published';
        $params = ['--provider' => 'Solutionplus\MicroService\MicroServiceServiceProvider'];

        if (File::exists(config_path('microservice.php'))) {
            if ($this->confirm('Config file already exists. Do you want to overwrite it?', false)) {
                $params['--force'] = true;
                $finishingMessage = 'MicroService configuration file is been overwritten';
                $this->info('Overwriting configuration file...');
            }
        }
        $this->call('vendor:publish', $params);
        $this->info($finishingMessage);
    }

    private function runMigration()
    {
        $this->warn('Make sure to set package configuration before migration or you will need to run this command again');
        if (! $this->confirm('Do you want to run migrate command now?', false)) return;

        $configDatabaseConnectionDriver = config('microservice.db_connection_name');
        if ($configDatabaseConnectionDriver == '') {
            $this->call('migrate', ['--path' => '/database/migrations/' . config('microservice.migration_sub_folder')]);
            $this->seedOneFakeMicroservice();
            return;
        }

        $currentConnectionDriver = DB::connection()->getPdo()?->getAttribute(\PDO::ATTR_DRIVER_NAME) ?? config('database.default');
        $migrationSubFolder = config('microservice.migration_sub_folder') != '' ? config('microservice.migration_sub_folder') . '/' : '';
        DB::setDefaultConnection($configDatabaseConnectionDriver);
        $this->call(
            'migrate',
            [
                '--database' => $configDatabaseConnectionDriver,
                '--path' => "database/migrations/{$migrationSubFolder}",
            ]
        );
        $this->seedOneFakeMicroservice();

        DB::setDefaultConnection($currentConnectionDriver);
    }

    private function seedOneFakeMicroservice()
    {
        if (! $this->confirm('Do you want to seed one fake microservice?', false)) return;
        $this->call('db:seed', ['--class' => MicroServiceMapsTableSeeder::class]);
    }
}
