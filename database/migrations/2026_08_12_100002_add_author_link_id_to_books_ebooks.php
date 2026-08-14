<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * বই ও ই-বুক টেবিলে লেখক ডিরেক্টরি লিংক কলাম যোগ।
 *
 * author_link_id → authors.id FK (nullable) — ডিরেক্টরি মোডে ব্যবহৃত হয়
 * author_name    → ফ্রি-টেক্সট (পূর্বের migration থেকে যোগ হয়েছে)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('books') && ! Schema::hasColumn('books', 'author_link_id')) {
            Schema::table('books', function (Blueprint $table) {
                $table->unsignedBigInteger('author_link_id')
                      ->nullable()
                      ->after('author_name')
                      ->comment('authors.id — লেখক ডিরেক্টরির সাথে লিংক');
            });
        }

        if (Schema::hasTable('ebooks') && ! Schema::hasColumn('ebooks', 'author_link_id')) {
            Schema::table('ebooks', function (Blueprint $table) {
                $table->unsignedBigInteger('author_link_id')
                      ->nullable()
                      ->after('author_name')
                      ->comment('authors.id — লেখক ডিরেক্টরির সাথে লিংক');
            });
        }
    }

    public function down(): void
    {
        foreach (['books', 'ebooks'] as $tbl) {
            if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'author_link_id')) {
                Schema::table($tbl, fn (Blueprint $t) => $t->dropColumn('author_link_id'));
            }
        }
    }
};
