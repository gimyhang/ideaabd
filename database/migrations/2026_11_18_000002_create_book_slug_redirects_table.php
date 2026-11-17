<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('book_slug_redirects')) {
            Schema::create('book_slug_redirects', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('book_id')->index();
                $table->string('old_slug', 255)->unique();
                $table->timestamps();
            });
        }

        // Bring every existing book to the "450-book-name" address; the old address redirects to it.
        // Safe to repeat: books that already have a numbered address are left alone.
        Artisan::call('books:number-slugs');
    }

    public function down(): void
    {
        // Put the old addresses back before dropping the table that remembers them.
        if (Schema::hasTable('book_slug_redirects') && Schema::hasTable('books')) {
            foreach (\Illuminate\Support\Facades\DB::table('book_slug_redirects')->orderBy('id')->get() as $r) {
                $taken = \Illuminate\Support\Facades\DB::table('books')->where('slug', $r->old_slug)->where('id', '!=', $r->book_id)->exists();
                if (! $taken) {
                    \Illuminate\Support\Facades\DB::table('books')->where('id', $r->book_id)->update(['slug' => $r->old_slug]);
                }
            }
        }
        Schema::dropIfExists('book_slug_redirects');
    }
};
