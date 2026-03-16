<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->index();
            $table->string('model')->index();
            $table->string('type')->index();
            $table->boolean('required')->default(0)->index();
            $table->string('placeholder')->nullable();
            $table->json('options')->nullable();
            $table->json('validation_rules')->nullable();
            $table->unique(['model', 'slug'], 'model_field_slug_unique');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('custom_field_id');
            $table->foreign('custom_field_id')->references('id')->on('custom_fields')->onDelete('cascade')->onUpdate('cascade');
            $table->morphs('model');
            $table->text('value')->nullable();
            $table->unique(['custom_field_id', 'model_type', 'model_id'], 'cf_values_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_fields');
    }
};
