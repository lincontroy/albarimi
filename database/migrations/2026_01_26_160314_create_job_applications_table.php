<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->text('cover_letter')->nullable();
            $table->string('resume_path')->nullable();
            $table->enum('status', ['pending', 'reviewing', 'shortlisted', 'rejected', 'hired'])->default('pending');
            $table->decimal('application_fee', 10, 2)->default(1000);
            $table->string('transaction_id')->nullable();
            $table->json('application_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('applied_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'job_id']); // Prevent multiple applications
            $table->index('user_id');
            $table->index('job_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};