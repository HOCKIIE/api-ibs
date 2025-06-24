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
        if (!Schema::hasTable('product_brand')) {
            Schema::create('product_brand', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset('utf8');
                $table->collation('utf8_general_ci');

                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('brand_id');

                $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
                $table->foreign('brand_id')->references('id')->on('brand')->onDelete('cascade');

                $table->primary(['product_id', 'brand_id']);

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_brand');
    }
};
