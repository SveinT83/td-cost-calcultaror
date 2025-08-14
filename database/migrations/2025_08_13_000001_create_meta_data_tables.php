<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetaDataTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create meta_fields table if it doesn't exist
        if (!Schema::hasTable('meta_fields')) {
            Schema::create('meta_fields', function (Blueprint $table) {
                $table->id();
                $table->string('key');
                $table->string('label');
                $table->text('description')->nullable();
                $table->string('type');
                $table->string('rules')->nullable();
                $table->json('default_value')->nullable();
                $table->json('options')->nullable();
                $table->string('module');
                $table->timestamps();
                
                // Create a unique index on key and module
                $table->unique(['key', 'module']);
            });
        }

        // Create meta_data table if it doesn't exist
        if (!Schema::hasTable('meta_data')) {
            Schema::create('meta_data', function (Blueprint $table) {
                $table->id();
                $table->string('parent_type');
                $table->bigInteger('parent_id');
                $table->string('key');
                $table->json('value')->nullable();
                $table->string('module');
                $table->timestamps();
                
                // Create indexes
                $table->index(['parent_type', 'parent_id']);
                $table->index(['key', 'module']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // We are not dropping tables here to avoid data loss
        // This should be done manually if needed
    }
}
