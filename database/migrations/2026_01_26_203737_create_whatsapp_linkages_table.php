<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_linkages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('phone_number')->unique();
            $table->string('verification_code')->nullable();
            $table->timestamp('verification_sent_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->enum('status', ['pending', 'verified', 'unlinked', 'blocked'])->default('pending');
            $table->integer('verification_attempts')->default(0);
            $table->timestamp('last_verification_attempt')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('phone_number');
            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index('verified_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_linkages');
    }
};