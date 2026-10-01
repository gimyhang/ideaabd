<?php

namespace Modules\SEO\Console\Commands;

use Illuminate\Console\Command;
use Modules\SEO\Services\AutoSeoScannerService;

class SeoScanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan all Books, Blog Posts, Authors, Publishers, E-books, Webzines and Pages to generate dynamic SEO tags, Schema JSON-LD, and audit scores';

    /**
     * Execute the console command.
     */
    public function handle(AutoSeoScannerService $scannerService): int
    {
        $this->info('🚀 Starting Idea Prakashan Automated SEO Scanner...');

        $summary = $scannerService->batchScanAll();

        $this->table(
            ['Content Type', 'Items Scanned & Optimized'],
            [
                ['Books (বইসমূহ)', $summary['books']],
                ['Blog Posts (আইডিয়াপত্র)', $summary['blog_posts']],
                ['Authors (লেখকবৃন্দ)', $summary['authors']],
                ['Publishers (প্রকাশকবৃন্দ)', $summary['publishers']],
                ['E-books (ই-বুক)', $summary['ebooks']],
                ['Webzines (ওয়েবজিন)', $summary['webzines']],
                ['Static Pages (পেজ)', $summary['pages']],
                ['Total Items (সর্বমোট)', $summary['total']],
            ]
        );

        $this->info("✅ Successfully generated and optimized SEO meta tags for {$summary['total']} items!");

        return Command::SUCCESS;
    }
}
