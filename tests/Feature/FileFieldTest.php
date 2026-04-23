<?php

namespace Salah\LaravelCustomFields\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Salah\LaravelCustomFields\Models\CustomField;
use Salah\LaravelCustomFields\Tests\Support\Models\Post;
use Salah\LaravelCustomFields\Tests\TestCase;

class FileFieldTest extends TestCase
{
    #[Test]
    public function it_can_upload_and_store_single_file()
    {
        Config::set('custom-fields.models', [
            'post' => Post::class,
        ]);
        Config::set('custom-fields.strict_validation', false);
        Storage::fake('public');

        $field = CustomField::create([
            'name' => 'Avatar',
            'type' => 'file',
            'model' => 'post',
        ]);

        $file = UploadedFile::fake()->image('avatar.jpg');

        // Simulate request
        $data = [
            'avatar' => $file,
        ];

        $model = new Post;
        $model->id = 1;
        $model->save();

        $model->saveCustomFields($data);

        // Assert DB
        $this->assertDatabaseHas('custom_field_values', [
            'custom_field_id' => $field->id,
            'model_id' => 1,
        ]);

        $value = $model->customFieldsValues->first();
        $decoded = json_decode($value->getAttributes()['value'], true);

        $this->assertArrayHasKey('path', $decoded);
        Storage::disk('public')->assertExists($decoded['path']);
    }

    #[Test]
    public function it_respects_configuration_for_disk_and_path()
    {
        Storage::fake('s3');
        Config::set('custom-fields.files.disk', 's3');
        Config::set('custom-fields.files.path', 'my-uploads');
        Config::set('custom-fields.models', [
            'post' => Post::class,
        ]);
        Config::set('custom-fields.strict_validation', false);

        $field = CustomField::create([
            'name' => 'Document',
            'type' => 'file',
            'model' => 'post',
        ]);

        $model = new Post;
        $model->id = 1;
        $model->save();

        $file = UploadedFile::fake()->create('doc.pdf');
        $model->saveCustomFields(['document' => $file]);

        $value = $model->customFieldsValues->first();
        $decoded = json_decode($value->getAttributes()['value'], true);

        // Verify Path contains configured folder
        $this->assertStringContainsString('my-uploads', $decoded['path']);

        // Verify File exists on configured disk
        Storage::disk('s3')->assertExists($decoded['path']);
    }

    #[Test]
    public function it_cleans_up_single_file_on_update()
    {
        Storage::fake('public');

        Config::set('custom-fields.models', [
            'post' => Post::class,
        ]);
        Config::set('custom-fields.strict_validation', false);

        $field = CustomField::create([
            'name' => 'Attachments',
            'type' => 'file',
            'model' => 'post',
        ]);

        $model = new Post;
        $model->id = 1;
        $model->save();

        // 1. Upload 1 file
        $file1 = UploadedFile::fake()->create('a.txt');
        $model->saveCustomFields(['attachments' => $file1]);

        $value1 = json_decode($model->fresh()->customFieldsValues->first()->getAttributes()['value'], true);
        $path1 = $value1['path'];

        Storage::disk('public')->assertExists($path1);

        // 2. Upload new file (Replace)
        $file2 = UploadedFile::fake()->create('c.txt');
        $model->updateCustomFields(['attachments' => $file2]);

        // Assert Old File Deleted
        Storage::disk('public')->assertMissing($path1);

        // Assert New File Exists
        $value2 = json_decode($model->fresh()->customFieldsValues->first()->getAttributes()['value'], true);
        Storage::disk('public')->assertExists($value2['path']);
    }

    #[Test]
    public function it_cleans_up_files_when_custom_field_is_force_deleted()
    {
        Config::set('custom-fields.models', [
            'post' => Post::class,
        ]);
        Config::set('custom-fields.strict_validation', false);
        Storage::fake('public');

        // 1. Create a File Custom Field
        $field = CustomField::create([
            'name' => 'Resume',
            'type' => 'file',
            'model' => 'post',
        ]);

        // 2. Create a Post and attach a file
        $model = new Post;
        $model->id = 1;
        $model->save();

        $file = UploadedFile::fake()->create('resume.pdf');
        $model->saveCustomFields(['resume' => $file]);

        // 3. Verify file exists
        $value = $model->customFieldsValues()->first();
        $this->assertNotNull($value);
        $decoded = json_decode($value->getAttributes()['value'], true);
        $path = $decoded['path'];
        Storage::disk('public')->assertExists($path);

        // 4. Force Delete the Custom Field
        // This should trigger cleanup of all associated values and their files
        $field->forceDelete();

        // 5. Verify file is deleted
        Storage::disk('public')->assertMissing($path);

        // 6. Verify value record is deleted
        $this->assertDatabaseMissing('custom_field_values', [
            'id' => $value->id,
        ]);
    }

    #[Test]
    public function it_cleans_up_old_file_when_overwriting_via_save_custom_fields()
    {
        Storage::fake('public');
        Config::set('custom-fields.models', ['post' => Post::class]);
        Config::set('custom-fields.strict_validation', false);

        CustomField::create(['name' => 'Photo', 'type' => 'file', 'model' => 'post']);
        $model = Post::create(['title' => 'Test']);

        // First upload
        $file1 = UploadedFile::fake()->image('old.jpg');
        $model->saveCustomFields(['photo' => $file1]);

        $oldPath = json_decode(
            $model->fresh()->customFieldsValues->first()->getAttributes()['value'],
            true
        )['path'];
        Storage::disk('public')->assertExists($oldPath);

        // Second upload via saveCustomFields (not updateCustomFields)
        $file2 = UploadedFile::fake()->image('new.jpg');
        $model->saveCustomFields(['photo' => $file2]);

        // Old file MUST be deleted
        Storage::disk('public')->assertMissing($oldPath);
    }

    #[Test]
    public function it_does_not_attempt_file_deletion_for_non_file_type_json_value()
    {
        Storage::fake('public');
        Config::set('custom-fields.models', ['post' => Post::class]);
        Config::set('custom-fields.strict_validation', false);

        $field = CustomField::create(['name' => 'Color', 'type' => 'color', 'model' => 'post']);
        $model = Post::create(['title' => 'Test']);

        // Store a JSON-like color value (starts with { to trick the old heuristic)
        $model->customFieldsValues()->create([
            'custom_field_id' => $field->id,
            'value' => '{"hex":"#ffffff"}',
        ]);

        // Delete the value — should NOT try to delete a storage file
        $value = $model->customFieldsValues()->first();
        $value->delete();

        // No exception and no fake storage interaction
        Storage::disk('public')->assertDirectoryEmpty('/');
    }
}
