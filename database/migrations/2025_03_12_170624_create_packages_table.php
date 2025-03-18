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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('site_count')->default(1);
            $table->integer('url_count')->default(10);
            $table->string('scan_frequency')->comment('MultiThread');
            $table->integer('processor_count')->default(1)->comment('1,2,4,8');
            $table->string('memory')->default('2Gb')->comment('2Gb, 4Gb, 6Gb, 8Gb, 16Gb, 32Gb');
            $table->boolean('is_active')->default(true);
            $table->decimal('price', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
