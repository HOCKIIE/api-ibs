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
        if(!Schema::hasTable('product')) {
            Schema::create('product', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset('utf8');
                $table->collation('utf8_general_ci');
                $table->id();
                $table->text('category')->nullable()->default(null)->comment('Category');
                $table->text('brand')->nullable()->default(null)->comment('Brand');
                $table->text('image')->nullable()->default(null)->comment('Product Image');
                $table->text('name_th')->nullable()->default(null)->comment('Category Name TH');
                $table->text('name_en')->nullable()->default(null)->comment('Category Name EN');
                $table->text('name_jp')->nullable()->default(null)->comment('Category Name JP');
                $table->mediumText('description_th')->nullable()->default(null)->comment('Category Description TH');
                $table->mediumText('description_en')->nullable()->default(null)->comment('Category Description EN');
                $table->mediumText('description_jp')->nullable()->default(null)->comment('Category Description JP');
                $table->longText('detail_th')->nullable()->default(null)->comment('Category Detail TH');
                $table->longText('detail_en')->nullable()->default(null)->comment('Category Detail EN');
                $table->longText('detail_jp')->nullable()->default(null)->comment('Category Detail JP');
                $table->boolean('status')->default(1)->comment('Category Status 1=Active,0=Inactive');
                $table->boolean('is_deleted')->default(0)->comment('Category Is Deleted 1=Deleted,0=Not Deleted');
                $table->dateTime('deleted_at')->nullable()->default(null)->comment('Category Deleted At');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
