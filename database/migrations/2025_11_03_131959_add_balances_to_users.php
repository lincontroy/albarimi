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
            $table->decimal('whatsapp_balance', 12, 2)->default(0)->after('remember_token');
            $table->decimal('total_earnings', 12, 2)->default(0)->after('whatsapp_balance');
            $table->decimal('total_withdrawn', 12, 2)->default(0)->after('total_earnings');
      
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn(['whatsapp_balance', 'total_earnings', 'total_withdrawn']);
        });
    }
};
