<?php

use App\Enums\QueueStatusEnum;
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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->index()->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('token')->nullable();
            $table->string('pagination_item_selector')->nullable();
            $table->string('product_item_selector');
            $table->boolean('include_subcategories')->default(false);
            $table->string('subcategory_selector')->nullable();
            $table->unsignedTinyInteger('status')->default(ScrapingStatusEnum::DISABLED->value);
            $table->string('queue_status', 50)->default(QueueStatusEnum::PENDING->value);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
