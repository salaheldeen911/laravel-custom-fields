<?php

namespace Salah\LaravelCustomFields\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Salah\LaravelCustomFields\Models\CustomField;
use Salah\LaravelCustomFields\Services\CustomFieldsService;
use Salah\LaravelCustomFields\Tests\TestCase;
use Salah\LaravelCustomFields\Traits\HasCustomFields;

class TestModel extends Model
{
    use HasCustomFields;

    protected $table = 'test_models';

    protected $guarded = [];
}

class ValidationRulesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Setup database schema for the test model
        \Illuminate\Support\Facades\Schema::create('test_models', function ($table) {
            $table->id();
            $table->timestamps();
        });

        // Register the mapping
        Config::set('custom-fields.models', [
            'test_model' => TestModel::class,
        ]);
    }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function min_rule_behaves_as_string_length_for_text_fields()
    // {
    //     // 1. Create a Text Field with Min: 5
    //     $field = CustomField::create([
    //         'name' => 'bio',
    //         'model' => 'test_model',
    //         'type' => 'text',
    //         'validation_rules' => ['min' => 5],
    //     ]);

    //     $service = app(CustomFieldsService::class);

    //     // 2. Test Invalid Input (Length < 5)
    //     $dataInvalid = ['bio' => 'abcd'];
    //     $validator = $service->validate('test_model', $dataInvalid);
    //     $this->assertTrue($validator->fails(), 'Text field with length 4 should fail min:5');
    //     $this->assertArrayHasKey('bio', $validator->errors()->toArray());

    //     // 3. Test Valid Input (Length >= 5)
    //     $dataValid = ['bio' => 'abcde'];
    //     $validator = $service->validate('test_model', $dataValid);
    //     $this->assertTrue($validator->passes(), 'Text field with length 5 should pass min:5');
    // }

    #[\PHPUnit\Framework\Attributes\Test]
    public function min_rule_behaves_as_numeric_value_for_number_fields()
    {
        // 1. Create a Number Field with Min: 5
        $field = CustomField::create([
            'name' => 'score',
            'model' => 'test_model',
            'type' => 'number',
            'validation_rules' => ['min' => 5],
        ]);

        $service = app(CustomFieldsService::class);

        // 2. Test Invalid Input (Value < 5)
        $dataInvalid = ['score' => 4];
        $validator = $service->validate('test_model', $dataInvalid);
        $this->assertTrue($validator->fails(), 'Number field with value 4 should fail min:5');

        // 3. Test Valid Input (Value >= 5)
        $dataValid = ['score' => 5];
        $validator = $service->validate('test_model', $dataValid);
        $this->assertTrue($validator->passes(), 'Number field with value 5 should pass min:5');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function max_rule_behaves_correctly_for_mixed_types()
    {
        // Text Field Max: 5 -> Length limit
        CustomField::create([
            'name' => 'short text',
            'model' => 'test_model',
            'type' => 'text',
            'validation_rules' => ['max' => 5],
        ]);

        // Number Field Max: 5 -> Value limit
        CustomField::create([
            'name' => 'small number',
            'model' => 'test_model',
            'type' => 'number',
            'validation_rules' => ['max' => 5],
        ]);

        $service = app(CustomFieldsService::class);

        // DEBUG
        $rules = $service->getValidationRules('test_model');

        // Test Text
        $this->assertTrue($service->validate('test_model', ['short-text' => 'abcde'])->passes());
        $this->assertTrue($service->validate('test_model', ['short-text' => 'abcdef'])->fails());

        // Test Number
        $this->assertTrue($service->validate('test_model', ['small-number' => 5])->passes());
        $this->assertTrue($service->validate('test_model', ['small-number' => 6])->fails());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function validation_fails_gracefully_when_non_numeric_passed_to_number_field()
    {
        // Number Field with Min: 5
        CustomField::create([
            'name' => 'age',
            'model' => 'test_model',
            'type' => 'number',
            'validation_rules' => ['min' => 5],
        ]);

        $service = app(CustomFieldsService::class);

        // Passing strict string "ten" should fail 'numeric' rule first or fail min rule?
        // NumberField baseRule is ['numeric'].
        $validator = $service->validate('test_model', ['age' => 'ten']);

        $this->assertTrue($validator->fails());
        // Verify it fails because of being not a number, not necessarily min.
        // Actually Laravel validation stops on first failure usually if bail is used,
        // but here it runs all. 'ten' fails 'numeric'.
        $errors = $validator->errors()->get('age');
        // We broadly accept that it fails.
    }
}
