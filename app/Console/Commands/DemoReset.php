<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DemoReset extends Command
{
    protected $signature   = 'demo:reset';
    protected $description = 'Reset the demo club to a clean seed state';

    public function handle(): void
    {
        $this->info('Resetting demo environment...');
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
        $this->info('Demo reset complete.');
    }
}
