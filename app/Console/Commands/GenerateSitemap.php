<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SitemapController;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate {--ping : Ping Google and Bing search engines after generation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and refresh all static & dynamic XML sitemaps for Google Search Console and Bing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating XML sitemaps for Google Search Console...');
        
        try {
            SitemapController::regenerateStaticSitemap();
            $this->info('✓ Master sitemap.xml and all sub-sitemaps generated successfully.');
        } catch (\Throwable $e) {
            $this->error('Failed to generate sitemap: ' . $e->getMessage());
            return Command::FAILURE;
        }

        if ($this->option('ping')) {
            $this->info('Pinging Google and Bing search engines...');
            try {
                $controller = new SitemapController();
                $resp = $controller->pingSearchEngines();
                $this->info('Search engines pinged.');
            } catch (\Throwable $e) {
                $this->warn('Search engine ping warning: ' . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
