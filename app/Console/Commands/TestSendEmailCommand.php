<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailJob;
use Illuminate\Console\Command;

class TestSendEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:send-mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $user = 'hans.teeroz@gmail.com';

        dispatch(new SendEmailJob($user));

        return 0;
    }
}