<?php

namespace Salah\LaravelCustomFields\Console\Commands;

use Illuminate\Console\Command;
use Salah\LaravelCustomFields\Models\CustomField;

class PruneCustomFieldsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom-fields:prune {--days= : Override the configuration for how many days to keep deleted records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete soft-deleted custom fields older than the configured threshold.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days') ?? config('custom-fields.pruning.prune_deleted_after_days', 30);

        if (! is_numeric($days) || $days < 0) {
            $this->error('Invalid days provided.');

            return 1;
        }

        $threshold = now()->subDays((int) $days);

        $this->info("Pruning custom fields deleted before {$threshold->toDateTimeString()}...");

        $count = CustomField::onlyTrashed()
            ->where('deleted_at', '<', $threshold)
            ->forceDelete();

        $this->info("{$count} custom fields permanently deleted.");

        return 0;
    }
}
