<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class cc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create model, controller';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $names = $this->ask('Enter the name of the the model)');
        $names = explode(',', $names);

        foreach ($names as $name) {
            $this->call('make:model', ['name' => $name]);
            $this->call('make:controller', ['name' => 'Api/' . $name . 'Controller', '--api' => true, '--resource' => true]);
        }

        $this->info('Done!');

    }
}
