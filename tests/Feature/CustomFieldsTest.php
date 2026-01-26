<?php

namespace Salah\LaravelCustomFields\Tests\Feature;

use Salah\LaravelCustomFields\Models\CustomField;
use Salah\LaravelCustomFields\Tests\TestCase;
use Salah\LaravelCustomFields\Traits\HasCustomFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class Post extends Model
{
    use HasCustomFields;

    protected $guarded = [];

    protected $table = 'posts';
}

class CustomFieldsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Define the model mapping in config for the test
        config()->set('custom-fields.models', [
            'post' => Post::class,
        ]);

        // Ensure rules are set
        config()->set('custom-fields.rules', [
            'string' => 'string|max:255',
            'number' => 'numeric',
            'email' => 'email',
        ]);

        config()->set('custom-fields.types', [
            'string' => 'String',
            'number' => 'Number',
        ]);
    }

    /** @test */
    public function it_can_create_a_custom_field()
    {
        $field = CustomField::create([
            'name' => 'extra_info',
            'model' => 'post', // This maps to Post::class via config
            'type' => 'string',
            'validation_rules' => ['required' => true],
        ]);

        $this->assertDatabaseHas('custom_fields', [
            'name' => 'extra_info',
            'type' => 'string',
        ]);

        // Verify mapping works
        $this->assertEquals('post', $field->model);
    }

    /** @test */
    public function it_validates_custom_fields()
    {
        // Create field
        CustomField::create([
            'name' => 'extra_info',
            'model' => 'post',
            'type' => 'string',
            'validation_rules' => ['required' => true],
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
            $this->assertArrayHasKey('extra_info', $e->errors());
        }

        // Now provide invalid data structure
        $request = new Request([
            'extra_info' => 'not an array',
        ]);
        try {
            Post::customFieldsValidation($request)->validate();
            $this->fail('Validation should have failed for non-array');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('extra_info', $e->errors());
        }
    }

    /** @test */
    public function it_can_store_and_retrieve_custom_field_values()
    {
        $field = CustomField::create([
            'name' => 'views_count',
            'model' => 'post',
            'type' => 'number',
            'validation_rules' => ['required' => false],
        ]);

        $post = Post::create(['title' => 'My Blog Post']);

        $data = [
            'views_count' => [
                'custom_field_id' => $field->id,
                'value' => 100,
            ],
        ];

        $request = new Request($data);

        $validator = Post::customFieldsValidation($request);
        $this->assertTrue($validator->passes());

        Post::storeCustomFieldValue($validator, $post);

        $this->assertDatabaseHas('custom_field_values', [
            'custom_field_id' => $field->id,
            'model_id' => $post->id,
            'value' => '100',
        ]);

        // Test Relationship
        $post->refresh();
        $this->assertCount(1, $post->customFieldsValues);

        // Test Helper Method
        $this->assertEquals(100, $post->custom('views_count'));
    }

    /** @test */
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
}
