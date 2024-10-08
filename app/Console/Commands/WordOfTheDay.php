<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class WordOfTheDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:word-of-the-day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Funny tip every day!';

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
    public function handle(): int
    {
        $words = [
            'aberration' => 'a state or condition markedly different from the norm',
            'convivial' => 'occupied with or fond of the pleasures of good company',
            'diaphanous' => 'so thin as to transmit light',
            'elegy' => 'a mournful poem; a lament for the dead',
            'ostensible' => 'appearing as such but not necessarily so'
        ];

        // Finding a random word
        $key = array_rand($words);
        $value = $words[$key];
        $user = 'hans.teeroz@gmail.com';

        Mail::raw("{$key} -> {$value}", function ($mail) use ($user) {
            $mail->from('info@mail.com');
            $mail->to($user)
                ->subject('Word of the Day');
        });

        $this->info('Word of the Day sent to All Users');

        return 0;
    }
}