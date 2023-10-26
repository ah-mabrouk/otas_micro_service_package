<?php

namespace Solutionplus\MicroService\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Solutionplus\MicroService\Helpers\MsHttp;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MicroServiceEstablishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ms:establish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Establish connection with another microservice';

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
        $destinationMicroserviceName = $this->ask('What is the destination name?');
        $destinationMicroserviceOrigin = $this->ask('What is the destination origin?');

        if ($destinationMicroserviceName == null || $destinationMicroserviceOrigin == null) return Command::FAILURE;

        MsHttp::establish($destinationMicroserviceName, $destinationMicroserviceOrigin);

        $this->info('Connection established successfully');

        return Command::FAILURE;
    }
}
