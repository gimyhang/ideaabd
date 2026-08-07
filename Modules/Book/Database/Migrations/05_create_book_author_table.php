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
        // ১. আগে শুধু পিভট টেবিল ও কলামগুলো তৈরি হবে (কোনো রিলেশনশিপ এরর আসবে না)
        Schema::create('book_author', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('author_id');
            $table->string('role')->default('author');
            $table->timestamps();

            $table->unique(['book_id', 'author_id', 'role']);
        });

        // ২. সিকিউর্ড ফরেন কী কন্সট্রেইন্ট যুক্ত করা
        Schema::table('book_author', function (Blueprint $table) {
            $table->foreign('book_id')->references('id')->on('books')->cascadeOnDelete();
            $table->foreign('author_id')->references('id')->on('authors')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_author');
    }
};