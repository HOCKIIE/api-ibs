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
        Schema::table('blog', function (Blueprint $table) {
            $table->text('image_th')->nullable()->default(null)->after('image')->comment('Image TH');
            $table->text('image_en')->nullable()->default(null)->after('image_th')->comment('Image EN');
            $table->text('image_ja')->nullable()->default(null)->after('image_en')->comment('Image JA');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('blog', function (Blueprint $table) {
            $table->dropColumn(['image_th','image_en','image_ja']);
        });
    }
};
