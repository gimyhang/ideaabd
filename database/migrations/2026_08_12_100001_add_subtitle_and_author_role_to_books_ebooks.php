<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * বই ও ই-বুক টেবিলে সাব-টাইটেল এবং লেখক/অনুবাদক/সম্পাদক ভূমিকার কলাম যোগ।
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('books')) {
            Schema::table('books', function (Blueprint $table) {
                if (! Schema::hasColumn('books', 'subtitle')) {
                    $table->string('subtitle', 500)->nullable()->after('title');
                }
                if (! Schema::hasColumn('books', 'author_name')) {
                    $table->string('author_name', 255)->nullable()->after('subtitle')
                          ->comment('লেখকের নাম (ফ্রি-টেক্সট)');
                }
                if (! Schema::hasColumn('books', 'author_role')) {
                    $table->string('author_role', 30)->nullable()->default('author')->after('author_name')
                          ->comment('author | translator | editor');
                }
            });
        }

        if (Schema::hasTable('ebooks')) {
            Schema::table('ebooks', function (Blueprint $table) {
                if (! Schema::hasColumn('ebooks', 'subtitle')) {
                    $table->string('subtitle', 500)->nullable()->after('title');
                }
                if (! Schema::hasColumn('ebooks', 'author_name')) {
                    $table->string('author_name', 255)->nullable()->after('subtitle')
                          ->comment('লেখকের নাম (ফ্রি-টেক্সট)');
                }
                if (! Schema::hasColumn('ebooks', 'author_role')) {
                    $table->string('author_role', 30)->nullable()->default('author')->after('author_name')
                          ->comment('author | translator | editor');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('books')) {
            Schema::table('books', function (Blueprint $table) {
                $table->dropColumn(array_filter(['subtitle', 'author_name', 'author_role'], fn ($c) => Schema::hasColumn('books', $c)));
            });
        }

        if (Schema::hasTable('ebooks')) {
            Schema::table('ebooks', function (Blueprint $table) {
                $table->dropColumn(array_filter(['subtitle', 'author_name', 'author_role'], fn ($c) => Schema::hasColumn('ebooks', $c)));
            });
        }
    }
};
