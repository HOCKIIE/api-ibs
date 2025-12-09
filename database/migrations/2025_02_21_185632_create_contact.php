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
        if (!Schema::hasTable('contact')) {
            Schema::create('contact', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->charset('utf8');
                $table->collation('utf8_general_ci');
                $table->id();
                $table->text('company')->nullable();
                $table->text('title')->nullable();
                $table->text('address')->nullable();
                $table->text('telephone')->nullable();
                $table->text('mobile')->nullable();
                $table->text('email')->nullable();
                $table->text('gmap')->nullable();
                $table->text('facebook')->nullable();
                $table->text('line')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact');
    }
};
