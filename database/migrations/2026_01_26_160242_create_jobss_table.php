<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobss', function (Blueprint $table) {
            $table->id();
            
            // Make it nullable first, then add constraint separately
            $table->unsignedBigInteger('category_id')->nullable();
            
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('salary_type')->default('monthly');
            $table->string('location')->nullable();
            $table->string('job_type')->default('full_time');
            $table->string('experience_level')->default('entry');
            $table->string('education_level')->nullable();
            $table->integer('vacancies')->default(1);
            $table->date('application_deadline')->nullable();
            $table->boolean('is_remote')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('view_count')->default(0);
            $table->integer('application_count')->default(0);
            $table->timestamps();
            $table->timestamp('published_at')->nullable();
            
            // // Add foreign key constraint separately
            // $table->foreign('category_id')
            //       ->references('id')
            //       ->on('job_categories')
            //       ->onDelete('cascade');
            
            // Indexes
            $table->index('category_id');
            $table->index('job_type');
            $table->index('experience_level');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobss');
    }
};