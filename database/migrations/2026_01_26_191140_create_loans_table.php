<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->decimal('interest_rate', 5, 2)->default(5.0); // 5% interest
            $table->integer('term_months')->default(12); // 12 months term
            $table->decimal('monthly_payment', 15, 2);
            $table->decimal('total_amount', 15, 2); // amount + interest
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('balance', 15, 2);
            $table->string('purpose')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'active', 'completed', 'defaulted'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->json('repayment_schedule')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->decimal('application_fee', 10, 2)->default(200);
            $table->string('transaction_id')->unique()->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};