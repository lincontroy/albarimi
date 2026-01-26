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
        Schema::create('starlink_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->decimal('monthly_fee', 10, 2);
            $table->integer('data_limit_gb')->nullable(); // NULL for unlimited
            $table->integer('speed_mbps');
            $table->string('duration'); // monthly, quarterly, annually
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('starlink_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('starlink_package_id')->constrained()->onDelete('cascade');
            $table->string('subscription_code')->unique();
            $table->decimal('amount_paid', 10, 2);
            $table->string('payment_method')->default('wallet');
            $table->enum('status', ['pending', 'active', 'suspended', 'cancelled', 'expired'])->default('pending');
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('transaction_id')->nullable();
            $table->json('payment_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('starlink_package_id');
            $table->index('status');
            $table->index('subscription_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starlink_subscriptions');
        Schema::dropIfExists('starlink_packages');
    }
};