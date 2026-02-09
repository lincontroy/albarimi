<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('package_type');
            $table->string('package_name');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('active');
            $table->timestamp('activated_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->json('features')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['package_type', 'status']);
            $table->index('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('packages');
    }
};