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
        if(!Schema::hasTable('blog_category'))
        {
            Schema::create('blog_category', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset('utf8');
                $table->collation('utf8_general_ci');

                $table->unsignedBigInteger('blog_id');
                $table->unsignedBigInteger('category_id');

                $table->foreign('blog_id')->references('id')->on('blog')->onDelete('cascade');
                $table->foreign('category_id')->references('id')->on('category')->onDelete('cascade');
                
                $table->primary(['blog_id', 'category_id']);

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_category');
    }
};
