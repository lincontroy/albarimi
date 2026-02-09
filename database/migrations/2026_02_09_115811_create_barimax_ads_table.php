<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('barimax_ads', function (Blueprint $table) {
            $table->id();
            $table->string('image_url');
            $table->string('hd_image_url');
            $table->string('caption');
            $table->integer('discount_percentage');
            $table->string('discount_code')->unique();
            $table->timestamp('valid_until');
            $table->boolean('is_active')->default(true);
            $table->boolean('ai_generated')->default(true);
            $table->integer('views_count')->default(0);
            $table->integer('clicks_count')->default(0);
            $table->json('tags')->nullable();
            $table->string('category')->default('general');
            $table->text('description')->nullable();
            $table->string('call_to_action')->default('Claim Offer');
            $table->string('background_color')->nullable();
            $table->string('text_color')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'valid_until']);
            $table->index(['category', 'ai_generated']);
            $table->index('discount_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('barimax_ads');
    }
};