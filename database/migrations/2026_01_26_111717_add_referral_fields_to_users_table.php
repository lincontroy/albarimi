<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code')->unique()->nullable()->after('id');
            $table->foreignId('referred_by')->nullable()->after('referral_code')->constrained('users')->onDelete('set null');
            $table->timestamp('last_active_at')->nullable()->after('agent_since');
            $table->decimal('total_earned_from_referrals', 10, 2)->default(0)->after('agent_salary');
            $table->integer('referral_count')->default(0)->after('total_earned_from_referrals');
            $table->integer('active_referral_count')->default(0)->after('referral_count');
            
            // Index for performance
            $table->index('referral_code');
            $table->index('referred_by');
            $table->index(['referred_by', 'last_active_at']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'referral_code',
                'referred_by',
                'last_active_at',
                'total_earned_from_referrals',
                'referral_count',
                'active_referral_count'
            ]);
        });
    }
};