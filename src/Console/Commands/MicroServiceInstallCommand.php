<?php

namespace Solutionplus\MicroService\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

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
            'MS_SECURE_REQUESTS_ONLY' => $this->appendToEnvContent('MS_SECURE_REQUESTS_ONLY', '"true"'),
            'GLOBAL_PROJECT_SECRET' => $this->appendToEnvContent('GLOBAL_PROJECT_SECRET', ''),
            'LOCAL_SECRET' => $this->appendToEnvContent('LOCAL_SECRET', generate_local_secret(16)),
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

    private function appendToEnvContent(string $envKey, string $envKeyValue = '')
    {
        $keyPosition = \strpos($this->envContent, "{$envKey}=");
        $endOfLinePosition = \strpos($this->envContent, "\n", $keyPosition);
        $oldValue = \substr($this->envContent, $keyPosition, $endOfLinePosition - $keyPosition);
        $envKeyValue = $keyPosition ? \explode('=', $oldValue)[1] : $envKeyValue;
        $this->envContent = ($keyPosition && $endOfLinePosition && $oldValue)
                            ? \str_replace($oldValue, "{$envKey}={$envKeyValue}", $this->envContent)
                            : $this->envContent . "{$envKey}={$envKeyValue}\n";
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
        DB::setDefaultConnection($currentConnectionDriver);
    }
}
