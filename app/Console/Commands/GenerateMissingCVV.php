<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateMissingCVV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:missing-cvv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = \App\Models\User::whereNull('card_cvv')->get();

        foreach ($users as $user) {
            $user->card_cvv = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $user->save();

            $this->info("Generated CVV for user ID {$user->id}: {$user->card_cvv}");
        }

        $this->info('CVV generation completed.');

        return 0;
    }
}
