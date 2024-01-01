<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReleaseRunCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:release';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run release steps';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $commands = [
            'npm i; npm run build',
            'composer update',
            'php artisan package:discover',
            'php artisan config:cache',
            'php artisan event:cache',
            'php artisan icons:cache',
            'php artisan route:cache',
            'php artisan view:cache',
            'php artisan optimize',
        ];

        $this->alert('Please, run these commands:');
        $this->info(implode(PHP_EOL, $commands));
        $this->newLine();
        $this->alert('Or in 1 line:');
        $this->info(implode('; ', $commands));
    }
}
