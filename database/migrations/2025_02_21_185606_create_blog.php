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
        if (!Schema::hasTable('blog')) {
            Schema::create('blog', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset('utf8');
                $table->collation('utf8_general_ci');
                $table->id();
                $table->text('image')->nullable()->default(null)->comment('Image');
                $table->text('title_th')->nullable()->default(null)->comment('Title TH');
                $table->text('title_en')->nullable()->default(null)->comment('Title EN');
                $table->text('title_ja')->nullable()->default(null)->comment('Title JA');
                $table->mediumText('description_th')->nullable()->default(null)->comment('Description TH');
                $table->mediumText('description_en')->nullable()->default(null)->comment('Description EN');
                $table->mediumText('description_ja')->nullable()->default(null)->comment('Description JA');
                $table->longText('detail_th')->nullable()->default(null)->comment('detail TH');
                $table->longText('detail_en')->nullable()->default(null)->comment('detail EN');
                $table->longText('detail_ja')->nullable()->default(null)->comment('detail JA');
                $table->boolean('status')->default(1)->comment('Status 1=Active,0=Inactive');
                $table->dateTime('published_at')->nullable()->default(null)->comment('Published At');
                $table->boolean('is_deleted')->default(0)->comment('Is Deleted 1=Deleted,0=Not Deleted');
                $table->dateTime('deleted_at')->nullable()->default(null)->comment('Deleted At');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog');
    }
};
