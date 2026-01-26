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
        Schema::create('visa_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('code')->unique();
            $table->enum('visa_type', ['tourist', 'business', 'student', 'work', 'transit'])->default('tourist');
            $table->string('country');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['available', 'purchased', 'activated', 'used', 'expired'])->default('available');
            $table->foreignId('purchased_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('visa_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('code');
            $table->index('visa_type');
            $table->index('status');
            $table->index('purchased_by');
        });
        
        // Create visa_code_purchases table for purchase history
        Schema::create('visa_code_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('visa_code_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->default('wallet');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->json('payment_details')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('visa_code_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_code_purchases');
        Schema::dropIfExists('visa_codes');
    }
};