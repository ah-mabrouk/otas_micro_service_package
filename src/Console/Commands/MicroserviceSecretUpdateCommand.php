<?php

namespace Solutionplus\MicroService\Console\Commands;

use Illuminate\Console\Command;
use Solutionplus\MicroService\Helpers\MsHttp;

class MicroserviceSecretUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ms:update-secret';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update project secret and notify other connections';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        MsHttp::announceSecretChange(secret: generate_local_secret(16));

        $this->info('Secret updated successfully');

        return Command::SUCCESS;
    }
}
