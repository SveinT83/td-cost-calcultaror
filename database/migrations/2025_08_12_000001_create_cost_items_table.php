<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('cost_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->enum('period', ['month', 'year', 'hour', 'minute']);
            $table->unsignedBigInteger('category_id')->nullable(); // No FK constraint
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('cost_items');
    }
};
