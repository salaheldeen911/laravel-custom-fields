<?php

namespace Salah\LaravelCustomFields\Tests;

use Salah\LaravelCustomFields\LaravelCustomFieldsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'CustomFields \\LaravelCustomFields\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelCustomFieldsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/../database/migrations/create_custom_fields_table.php';
        $migration->up();

        \Illuminate\Support\Facades\Schema::create('posts', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });
    }
}
