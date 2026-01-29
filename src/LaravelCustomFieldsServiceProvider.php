<?php

namespace Salah\LaravelCustomFields;

use Salah\LaravelCustomFields\FieldTypeRegistry;
use Salah\LaravelCustomFields\ValidationRuleRegistry;
use Salah\LaravelCustomFields\Commands\InstallCommand;
use Salah\LaravelCustomFields\Commands\LaravelCustomFieldsCommand;
use Salah\LaravelCustomFields\FieldTypes\CheckboxField;
use Salah\LaravelCustomFields\FieldTypes\NumberField;
use Salah\LaravelCustomFields\FieldTypes\PhoneField;
use Salah\LaravelCustomFields\FieldTypes\EmailField;
use Salah\LaravelCustomFields\FieldTypes\SelectField;
use Salah\LaravelCustomFields\FieldTypes\TextField;
use Salah\LaravelCustomFields\FieldTypes\DateField;
use Salah\LaravelCustomFields\FieldTypes\UrlField;
use Salah\LaravelCustomFields\ValidationRules\EmailRule;
use Salah\LaravelCustomFields\ValidationRules\UrlRule;
use Salah\LaravelCustomFields\ValidationRules\AfterDateRule;
use Salah\LaravelCustomFields\ValidationRules\BeforeDateRule;
use Salah\LaravelCustomFields\ValidationRules\AfterOrEqualDateRule;
use Salah\LaravelCustomFields\ValidationRules\BeforeOrEqualDateRule;
use Salah\LaravelCustomFields\ValidationRules\DateFormatRule;
use Salah\LaravelCustomFields\ValidationRules\AlphaDashRule;
use Salah\LaravelCustomFields\ValidationRules\AlphaNumRule;
use Salah\LaravelCustomFields\ValidationRules\AlphaRule;
use Salah\LaravelCustomFields\ValidationRules\MaxRule;
use Salah\LaravelCustomFields\ValidationRules\MinRule;
use Salah\LaravelCustomFields\ValidationRules\NotRegexRule;
use Salah\LaravelCustomFields\ValidationRules\PhoneRule;
use Salah\LaravelCustomFields\ValidationRules\RegexRule;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelCustomFieldsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-custom-fields')
            ->hasConfigFile('custom-fields')
            ->hasMigration('create_custom_fields_table')
            ->hasCommand(LaravelCustomFieldsCommand::class)
            ->hasCommand(InstallCommand::class);

        // Always register views, user might validly use them even in API mode if they want emails etc,
        // or we just enable them if web is enabled. For now, let's keep it simple and always register views
        // if the package has them. The config optimization is better done via route loading.
        $package->hasViews('custom-fields');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(FieldTypeRegistry::class, function () {
            $registry = new FieldTypeRegistry;
            $registry->register(new TextField);
            $registry->register(new DateField);
            $registry->register(new SelectField);
            $registry->register(new CheckboxField);
            $registry->register(new NumberField);
            $registry->register(new PhoneField);
            $registry->register(new EmailField);
            $registry->register(new UrlField);

            return $registry;
        });

        $this->app->singleton(ValidationRuleRegistry::class, function () {
            $registry = new ValidationRuleRegistry;
            $registry->register(new MinRule);
            $registry->register(new MaxRule);
            $registry->register(new RegexRule);
            $registry->register(new NotRegexRule);
            $registry->register(new AlphaRule);
            $registry->register(new AlphaDashRule);
            $registry->register(new AlphaNumRule);
            $registry->register(new PhoneRule);
            $registry->register(new EmailRule);
            $registry->register(new UrlRule);
            $registry->register(new AfterDateRule);
            $registry->register(new BeforeDateRule);
            $registry->register(new AfterOrEqualDateRule);
            $registry->register(new BeforeOrEqualDateRule);
            $registry->register(new DateFormatRule);

            return $registry;
        });
    }

    public function packageBooted(): void
    {
        if (config('custom-fields.routing.web.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        if (config('custom-fields.routing.api.enabled', false)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }
    }
}
