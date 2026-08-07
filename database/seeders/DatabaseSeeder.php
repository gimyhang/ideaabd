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
        // Background Jobs Table (অর্ডার কনফার্মেশন ইমেইল বা ইনভয়েস পিডিএফ তৈরির মতো কাজগুলো সাইটকে স্লো না করে ব্যাকগ্রাউন্ডে করার জন্য)
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        // Job Batches Table (একসাথে হাজার হাজার কাস্টমারকে প্রমোশনাল ইমেইল পাঠানো বা বাল্ক ডাটা প্রসেস করার ট্র্যাক রাখার জন্য)
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        // Failed Jobs Table (সার্ভার বা নেটওয়ার্ক এররের কারণে কোনো ব্যাকগ্রাউন্ড টাস্ক ফেইল করলে তার বিস্তারিত লগ এখানে জমা হয়)
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};