<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('application_id')->unique();
            $table->enum('type', ['professional', 'skill', 'service', 'product', 'business'])->default('professional');
            $table->string('title');
            $table->text('description');
            $table->json('skills')->nullable();
            $table->json('documents')->nullable();
            $table->json('links')->nullable();
            $table->decimal('package_fee', 10, 2)->default(3500);
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected', 'active', 'expired'])->default('pending');
            $table->text('review_notes')->nullable();
            $table->integer('rating')->nullable();
            $table->integer('views')->default(0);
            $table->integer('endorsements_count')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('type');
            $table->index('status');
            $table->index('application_id');
            $table->index(['user_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('endorsements');
    }
};