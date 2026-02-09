<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('rating')->default(5);
            $table->string('title')->nullable();
            $table->text('comment');
            $table->enum('status', ['draft', 'pending', 'published', 'rejected'])->default('pending');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['status', 'is_featured']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
};