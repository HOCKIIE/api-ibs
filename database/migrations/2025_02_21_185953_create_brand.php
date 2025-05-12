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
        if(!Schema::hasTable('brand')) {
            Schema::create('brand', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset('utf8');
                $table->collation('utf8_general_ci');
                $table->id();
                $table->integer('category')->nullable()->default(null)->comment('Category ID');
                $table->text('image')->nullable()->default(null)->comment('Brand Image');
                $table->text('title_th')->nullable()->default(null)->comment('Title TH');
                $table->text('title_en')->nullable()->default(null)->comment('Title EN');
                $table->text('title_jp')->nullable()->default(null)->comment('Title JP');
                $table->mediumText('description_th')->nullable()->default(null)->comment('Brand Description TH');
                $table->mediumText('description_en')->nullable()->default(null)->comment('Brand Description EN');
                $table->mediumText('description_jp')->nullable()->default(null)->comment('Brand Description JP');
                $table->boolean('status')->default(1)->comment('Brand Status 1=Active,0=Inactive');
                $table->boolean('is_deleted')->default(0)->comment('Brand Is Deleted 1=Deleted,0=Not Deleted');
                $table->dateTime('deleted_at')->nullable()->default(null)->comment('Brand Deleted At');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand');
    }
};
