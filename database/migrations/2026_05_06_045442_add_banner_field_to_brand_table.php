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
        Schema::table('brand', function (Blueprint $table) {
            if (!Schema::hasColumn('brand', 'draftId')) $table->text('draftId')->nullable()->comment('Draft ID');
            if (!Schema::hasColumn('brand', 'banner')) $table->text('banner')->nullable()->comment('Banner Image');
            if (!Schema::hasColumn('brand', 'is_iframe')) $table->boolean('is_iframe')->default(false)->comment('Is Iframe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brand', function (Blueprint $table) {
            $table->dropColumn('draftId');
            $table->dropColumn('banner');
            $table->dropColumn('is_iframe');
        });
    }
};
