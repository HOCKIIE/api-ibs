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
        if (!Schema::hasTable('owner')) {
            Schema::create('owner', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset('utf8');
                $table->collation('utf8_general_ci');
                $table->id();
                $table->text('logo')->nullable();
                $table->text('title')->nullable();
                $table->text('email')->nullable();
                $table->text('address')->nullable();
                $table->text('phone')->nullable();
                $table->text('mobile')->nullable();
                $table->longText('gmap')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner');
    }
};
