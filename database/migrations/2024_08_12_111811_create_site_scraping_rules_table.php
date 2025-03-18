<?php

use App\Enums\ScrapingStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_scraping_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->index()->constrained()->cascadeOnDelete();

            $table->string('field');
            $table->string('selector');
            $table->string('type')->nullable();
            $table->unsignedTinyInteger('status')->default(ScrapingStatusEnum::DISABLED->value);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_scraping_rules');
    }
};
