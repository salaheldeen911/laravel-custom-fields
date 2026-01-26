<?php

namespace Salah\LaravelCustomFields\Commands;

use Illuminate\Console\Command;

class LaravelCustomFieldsCommand extends Command
{
    public $signature = 'laravel-custom-fields';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
