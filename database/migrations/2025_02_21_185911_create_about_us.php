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
        if(!Schema::hasTable('about_us')) {
            Schema::create('about_us', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset('utf8');
                $table->collation('utf8_general_ci');
                $table->id();
                $table->text('image')->nullable();
                $table->text('name_th')->nullable();
                $table->text('name_en')->nullable();
                $table->text('name_jp')->nullable();
                $table->longText('detail_th')->nullable();
                $table->longText('detail_en')->nullable();
                $table->longText('detail_jp')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_us');
    }
};
