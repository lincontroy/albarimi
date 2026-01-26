<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('certification_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('package_type', ['certification', 'access_code', 'verification']);
            $table->string('package_name');
            $table->decimal('amount', 10, 2);
            $table->string('certification_code')->unique()->nullable();
            $table->string('access_code')->unique()->nullable();
            $table->string('verification_code')->unique()->nullable();
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('package_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Use shorter index names
            $table->index(['user_id', 'status'], 'cert_user_status');
            $table->index(['certification_code'], 'cert_code_idx');
            $table->index(['access_code'], 'access_code_idx');
            $table->index(['verification_code'], 'verification_code_idx');
            $table->index(['expires_at'], 'expires_at_idx');
            
            // Composite index with shorter name
            $table->index(['certification_code', 'access_code', 'verification_code'], 'cert_codes_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('certification_purchases');
    }
};