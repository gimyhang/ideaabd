<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetupDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ideaabd:setup-demo {--reset : Reset database before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'সেটআপ করুন ideaabd ডেমো ডেটা এবং ফাইল';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 ideaabd ডেমো ডেটা সেটআপ শুরু করছি...');
        $this->newLine();

        // Reset option
        if ($this->option('reset')) {
            $this->info('🔄 ডেটাবেস রিসেট করছি...');
            $this->call('migrate:fresh');
            $this->newLine();
        }

        // Run migrations
        $this->info('📦 ডেটাবেস মাইগ্রেশন চালাচ্ছি...');
        $this->call('migrate', ['--quiet' => true]);
        $this->newLine();

        // Seed demo data
        $this->info('🌱 ডেমো ডেটা সিড করছি...');
        $this->call('db:seed', ['--class' => 'DemoDataSeeder', '--quiet' => true]);
        $this->newLine();

        // Create directories
        $this->info('📁 প্রয়োজনীয় ডিরেক্টরি তৈরি করছি...');
        @mkdir(storage_path('app/ebooks'), 0755, true);
        @mkdir(storage_path('app/books'), 0755, true);
        @mkdir(public_path('images'), 0755, true);
        $this->line('   ✓ storage/app/ebooks/');
        $this->line('   ✓ storage/app/books/');
        $this->line('   ✓ public/images/');
        $this->newLine();

        // Summary
        $this->info('✅ সেটআপ সম্পন্ন!');
        $this->newLine();
        
        $this->info('📊 সংযোজিত ডেটা:');
        $this->line('   • ' . DB::table('authors')->count() . ' জন লেখক');
        $this->line('   • ' . DB::table('publishers')->count() . ' টি প্রকাশনী');
        $this->line('   • ' . DB::table('books')->count() . ' টি বই');
        $this->newLine();

        $this->info('🌐 এখন অ্যাপ চালু করুন:');
        $this->line('   php artisan serve');
        $this->newLine();

        $this->info('👤 ডেভেলপার: Masud Rana Shakil');
        $this->info('📱 প্ল্যাটফর্ম: ideaabd');
    }
}
