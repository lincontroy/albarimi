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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->boolean('is_agent')->default(false)->after('total_withdrawn');
            $table->timestamp('agent_since')->nullable()->after('is_agent');
            $table->decimal('agent_salary', 10, 2)->default(0)->after('agent_since');
            $table->decimal('agent_bonus', 10, 2)->default(0)->after('agent_salary');
            $table->decimal('deposit_balance', 12, 2)->default(0)->after('agent_bonus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn(['is_agent', 'agent_since', 'agent_salary', 'agent_bonus', 'deposit_balance']);
        });
    
    }
};
