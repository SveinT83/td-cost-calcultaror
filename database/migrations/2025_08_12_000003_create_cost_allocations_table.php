<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('cost_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('cost_item_id');
            $table->enum('allocation_type', ['per_user', 'fixed', 'per_resource_unit']);
            $table->decimal('allocation_value', 12, 4);
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('cost_allocations');
    }
};
