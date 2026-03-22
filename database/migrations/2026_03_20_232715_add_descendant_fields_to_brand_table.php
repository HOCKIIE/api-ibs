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
            if (!Schema::hasColumn('brand', 'descendant_th')) $table->json('descendant_th')->nullable()->comment('Descendant[]');
            if (!Schema::hasColumn('brand', 'descendant_en')) $table->json('descendant_en')->nullable()->comment('Descendant[]');
            if (!Schema::hasColumn('brand', 'descendant_ja')) $table->json('descendant_ja')->nullable()->comment('Descendant[]');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brand', function (Blueprint $table) {
            $table->dropColumn(['descendant_th','descendant_en','descendant_ja']);
        });
    }
};
