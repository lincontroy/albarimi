<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bonus_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('bonus_type', ['welcome_bonus', 'minor_bonus', 'pro_bonus', 'mega_bonus']);
            $table->decimal('bonus_amount', 10, 2);
            $table->decimal('claim_cost', 10, 2);
            $table->string('status')->default('pending'); // pending, claimed, failed
            $table->text('failure_reason')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'bonus_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bonus_claims');
    }
};