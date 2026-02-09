<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('online_work_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('platform');
            $table->string('platform_name');
            $table->decimal('amount', 10, 2);
            $table->json('account_details')->nullable();
            $table->json('login_credentials')->nullable();
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('active');
            $table->timestamp('purchased_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['platform', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('online_work_accounts');
    }
};