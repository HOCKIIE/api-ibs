<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('brand_category')) {
            Schema::create('brand_category', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset('utf8');
                $table->collation('utf8_general_ci');

                $table->unsignedBigInteger('brand_id');
                $table->unsignedBigInteger('category_id');

                $table->foreign('brand_id')->references('id')->on('brand')->onDelete('cascade');
                $table->foreign('category_id')->references('id')->on('category')->onDelete('cascade');

                $table->primary(['brand_id', 'category_id']);

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_category');
    }
};
