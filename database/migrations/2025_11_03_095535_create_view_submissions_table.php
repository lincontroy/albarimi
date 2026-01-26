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
        Schema::create('view_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('whatsapp_number', 15);
            $table->integer('views_count');
            $table->string('platform'); // YouTube, Facebook, Instagram, TikTok
            $table->decimal('earned_amount', 10, 2);
            $table->string('screenshot_path');
            $table->enum('status', ['pending', 'approved', 'rejected', 'processing', 'cancelled'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'status']);
            $table->index('status');
            $table->index('platform');
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('view_submissions');
    }
};