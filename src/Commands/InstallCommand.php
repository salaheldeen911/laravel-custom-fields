<?php

namespace Salah\LaravelCustomFields\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    public $signature = 'custom-fields:install';

    public $description = 'Install the Laravel Custom Fields package';

    public function handle(): int
    {
        $this->info('Installing Laravel Custom Fields...');

        $choice = $this->choice(
            'How do you want to use this package?',
            ['Blade (Full UI)', 'API Only (Headless)'],
            0
        );

        $hasViews = $choice === 'Blade (Full UI)';

        // Publish config
        $this->call('vendor:publish', [
            '--tag' => 'custom-fields-config',
            '--force' => true,
        ]);

        // Update config file based on choice
        $configFile = config_path('custom-fields.php');

        if (File::exists($configFile)) {
            $content = File::get($configFile);

            if ($hasViews) {
                // Choice: Blade (Full UI)
                // We want: Web=True, API=False (or True? Let's stick to defaults which is Web=True, API=False)
                // The default published config is Web=True, API=False.
                // So we don't strictly need to change anything if we trust the default.
                // But let's ensure Web is True just in case.

                $content = preg_replace(
                    "/'web'\s*=>\s*\[\s*'enabled'\s*=>\s*false/",
                    "'web' => [\n'enabled' => true",
                    $content
                );
            } else {
                // Choice: API Only
                // We want: Web=False, API=True

                // Disable Web
                $content = preg_replace(
                    "/'web'\s*=>\s*\[\s*'enabled'\s*=>\s*true/",
                    "'web' => [\n'enabled' => false",
                    $content
                );

                // Enable API
                $content = preg_replace(
                    "/'api'\s*=>\s*\[\s*'enabled'\s*=>\s*false/",
                    "'api' => [\n'enabled' => true",
                    $content
                );
            }

            File::put($configFile, $content);
        }

        // Publish migrations
        $this->call('vendor:publish', [
            '--tag' => 'custom-fields-migrations',
        ]);

        if ($hasViews) {
            if ($this->confirm('Do you want to publish the views for customization?', false)) {
                $this->info('Publishing views...');
                $this->call('vendor:publish', [
                    '--tag' => 'custom-fields-views',
                ]);
            }
        }

        $this->info('Laravel Custom Fields installed successfully.');

        if ($hasViews) {
            $this->info('Blade views and routes are enabled.');
        } else {
            $this->info('Running in API mode. Views and web routes are disabled.');
        }

        return self::SUCCESS;
    }
}
