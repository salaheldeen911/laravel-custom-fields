<?php

namespace Salah\LaravelCustomFields\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Salah\LaravelCustomFields\Models\CustomField;
use Salah\LaravelCustomFields\Services\CustomFieldsService;
use Salah\LaravelCustomFields\Tests\Support\Models\Post;
use Salah\LaravelCustomFields\Tests\TestCase;

class CustomFieldsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Define the model mapping in config for the test
        config()->set('custom-fields.models', [
            'post' => Post::class,
        ]);
    }

    #[Test]
    public function it_can_create_a_custom_field()
    {
        // Reverting to previous working state for now to pass tests
        $field = CustomField::create([
            'name' => 'extra_info',
            'model' => 'post',
            'type' => 'string',
        ]);

        $this->assertDatabaseHas('custom_fields', [
            'name' => 'extra_info',
            'type' => 'string',
        ]);
    }

    #[Test]
    public function it_validates_custom_fields()
    {
        $name = 'extra_info';
        $slug = Str::slug($name);
        // Create field
        CustomField::create([
            'name' => $name,
            'model' => 'post',
            'type' => 'text',
            'required' => true,
        ]);

        $post = Post::create(['title' => 'Test Post']);

        // Mock Request with missing required field
        $request = new Request([
            'title' => 'Test Post',
        ]);

        // We expect validation failure
        try {
            Post::customFieldsValidation($request)->validate();

            $this->fail('Validation should have failed');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($slug, $e->errors());
        }

        // Now provide invalid data structure
        $request = new Request([
            $slug => ['array'],
        ]);
        try {
            Post::customFieldsValidation($request)->validate();
            $this->fail('Validation should have failed for non-string');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($slug, $e->errors());
        }
    }

    #[Test]
    public function it_can_store_and_retrieve_custom_field_values()
    {
        $name = 'views count';
        $slug = Str::slug($name);
        $field = CustomField::create([
            'name' => $name,
            'model' => 'post',
            'type' => 'number',
            'validation_rules' => ['required' => false],
        ]);

        $post = Post::create(['title' => 'My Blog Post']);

        $data = [
            $slug => 100,
        ];

        $request = new Request($data);

        $validator = Post::customFieldsValidation($request);
        $this->assertTrue($validator->passes());

        $post->saveCustomFields($data);

        $this->assertDatabaseHas('custom_field_values', [
            'custom_field_id' => $field->id,
            'model_id' => $post->id,
            'value' => '100',
        ]);

        // Test Relationship
        $post->refresh();
        $this->assertCount(1, $post->customFieldsValues);

        // Test Helper Method
        $this->assertEquals(100, $post->custom($slug));
    }

    #[Test]
    public function it_can_filter_by_custom_field()
    {
        $field = CustomField::create([
            'name' => 'status',
            'model' => 'post',
            'type' => 'string',
        ]);

        $post1 = Post::create(['title' => 'Post 1']);
        $post1->customFieldsValues()->create([
            'custom_field_id' => $field->id,
            'value' => 'active',
        ]);

        $post2 = Post::create(['title' => 'Post 2']);
        $post2->customFieldsValues()->create([
            'custom_field_id' => $field->id,
            'value' => 'inactive',
        ]);

        $results = Post::whereCustomField('status', 'active')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Post 1', $results->first()->title);
    }

    #[Test]
    public function it_resets_validated_state_after_save_custom_fields()
    {
        config()->set('custom-fields.models', ['post' => Post::class]);
        config()->set('custom-fields.strict_validation', false);

        CustomField::create(['name' => 'Bio', 'type' => 'text', 'model' => 'post']);
        $model = Post::create(['title' => 'Test']);

        $service = app(CustomFieldsService::class);
        $service->markAsValidated();
        $this->assertTrue($service->isValidated());

        $model->saveCustomFields(['bio' => 'hello']);

        $this->assertFalse($service->isValidated());
    }

    #[Test]
    public function it_allows_nullable_file_on_update_if_already_exists()
    {
        $name = 'attachment';
        $slug = Str::slug($name);

        // Create field
        $field = CustomField::create([
            'name' => $name,
            'model' => 'post',
            'type' => 'file',
            'required' => true,
        ]);

        $post = Post::create(['title' => 'Test Post']);

        // 1. Initial validation should fail (no file provided, required=true)
        $request = new Request(['title' => 'Updated Post']);
        $rules = Post::getCustomFieldRules(); // Static call, no model instance
        $validator = validator($request->all(), $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey($slug, $validator->errors()->toArray());

        // 2. Add a value to the database to simulate existing file
        $post->customFieldsValues()->create([
            'custom_field_id' => $field->id,
            'value' => json_encode(['path' => 'uploads/test.jpg', 'name' => 'test.jpg']),
        ]);

        // 3. Validation with model instance should pass even if file is missing in request
        $rulesWithModel = Post::getCustomFieldRules($post);
        $validatorWithModel = validator($request->all(), $rulesWithModel);

        $this->assertTrue($validatorWithModel->passes(), 'Validation should pass on update if file already exists');
    }
}
