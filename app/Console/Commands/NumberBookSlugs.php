<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Book\Models\Book;
use Modules\Book\Models\BookSlugRedirect;

/**
 * Gives every book the "450-book-name" address (the number is the book's own id, which the QR code
 * carries) and remembers the old address so it keeps redirecting. Run by the migration; safe to run again.
 */
class NumberBookSlugs extends Command
{
    protected $signature = 'books:number-slugs {--dry-run : only count what would change}';

    protected $description = 'Give existing books the number + name address and redirect the old ones';

    public function handle(): int
    {
        if (! Schema::hasTable('books') || ! Schema::hasTable('book_slug_redirects')) {
            $this->warn('books / book_slug_redirects table missing; nothing to do.');

            return self::SUCCESS;
        }

        $dry = (bool) $this->option('dry-run');
        $changed = 0;
        $skipped = 0;

        Book::withTrashed()->select(['id', 'title', 'title_en', 'slug'])->orderBy('id')->chunkById(200, function ($books) use ($dry, &$changed, &$skipped) {
            foreach ($books as $book) {
                $current = (string) $book->slug;
                $numbered = $book->numberedSlug();

                // already "450-…" (or just "450"): nothing to do
                if ($current === $numbered || $current === (string) $book->id || str_starts_with($current, $book->id . '-')) {
                    continue;
                }

                $taken = Book::withTrashed()->where('slug', $numbered)->where('id', '!=', $book->id)->exists();
                if ($taken) {
                    $skipped++;
                    continue;
                }

                if (! $dry) {
                    DB::transaction(function () use ($book, $current, $numbered) {
                        DB::table('books')->where('id', $book->id)->update(['slug' => $numbered]);
                    });
                    \App\Services\BookAddressSeo::moved($book->id, $current, $numbered);
                }
                $changed++;
            }
        });

        if (! $dry && $changed > 0) {
            // the static sitemap files must list the new addresses, not redirecting ones
            try {
                \Illuminate\Support\Facades\Artisan::call('sitemap:generate');
                $this->line('Sitemaps regenerated.');
            } catch (\Throwable $e) {
                $this->warn('Run  php artisan sitemap:generate  to refresh the sitemaps: ' . $e->getMessage());
            }
        }

        $this->info(($dry ? 'Would change ' : 'Changed ') . "{$changed} book address(es)" . ($skipped ? ", skipped {$skipped} (address already taken)" : '') . '.');

        return self::SUCCESS;
    }
}
