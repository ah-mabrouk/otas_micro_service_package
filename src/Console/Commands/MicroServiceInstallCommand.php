<?php

namespace Solutionplus\MicroService\Console\Commands;

use Illuminate\Console\Command;
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

    protected $envKeys = [
        'GLOBAL_PROJECT_SECRET',
        'LOCAL_SECRET',
    ];

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
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Publishing configuration...');

        if (! $this->configExists('microservice.php')) {
            $this->publishConfiguration();
            $this->info('MicroService configuration file is published');
        } else {
            if ($this->shouldOverwriteConfig()) {
                $this->info('Overwriting configuration file...');
                $this->publishConfiguration(true);
                $this->info('MicroService configuration file is been overwritten');
            } else {
                $this->info('Existing configuration is not overwritten for some reason');
            }
        }

        $this->info('Addint package .env keys');
        $this->setPackageEnvKeys();
        $this->info("The keys[" . \implode(', ', $this->envKeys). "] has added to project .env file");

        $this->info('Caching configs...');
        $this->call('config:cache');

        $this->info('Running migrate command...');
        $this->call('migrate');

        return Command::SUCCESS;
    }

    private function configExists($fileName)
    {
        return File::exists(config_path($fileName));
    }

    private function shouldOverwriteConfig()
    {
        return $this->confirm(
            'Config file already exists. Do you want to overwrite it?',
            false
        );
    }

    private function publishConfiguration($forcePublish = false)
    {
        $params = [
            '--provider' => 'Solutionplus\MicroService\MicroServiceServiceProvider',
        ];

        if ($forcePublish === true) {
            $params['--force'] = true;
        }

       $this->call('vendor:publish', $params);
    }

    private function setPackageEnvKeys()
    {
        for ($i = 0; $i < \count($this->envKeys); $i++) {
            $this->envContent = $this->setPackageEnvSingleKey($this->envKeys[$i], $this->envKeys[$i] === 'GLOBAL_PROJECT_SECRET' ? '' : generate_local_secret(16));
        }
        return \file_put_contents($this->envFile, $this->envContent);
    }

    private function setPackageEnvSingleKey(string $envKey, string $envKeyValue = '')
    {
        $keyPosition = \strpos($this->envContent, "{$envKey}=");
        $endOfLinePosition = \strpos($this->envContent, "\n", $keyPosition);
        $oldValue = \substr($this->envContent, $keyPosition, $endOfLinePosition - $keyPosition);
        $envKeyValue = $envKey === 'GLOBAL_PROJECT_SECRET' && $keyPosition ? \explode('=', $oldValue)[1] : $envKeyValue;

        return ($keyPosition && $endOfLinePosition && $oldValue)
                    ? \str_replace($oldValue, "{$envKey}={$envKeyValue}", $this->envContent)
                    : $this->envContent . "{$envKey}={$envKeyValue}\n";
    }
}
