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
        // 1. Add KDP and Wallet columns to authors table
        Schema::table('authors', function (Blueprint $table) {
            if (!Schema::hasColumn('authors', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('authors', 'royalty_percentage')) {
                $table->decimal('royalty_percentage', 5, 2)->default(50.00)->after('is_active');
            }
            if (!Schema::hasColumn('authors', 'wallet_balance')) {
                $table->decimal('wallet_balance', 10, 2)->default(0.00)->after('royalty_percentage');
            }
            if (!Schema::hasColumn('authors', 'total_payout_withdrawn')) {
                $table->decimal('total_payout_withdrawn', 10, 2)->default(0.00)->after('wallet_balance');
            }
            if (!Schema::hasColumn('authors', 'payout_account_type')) {
                $table->string('payout_account_type', 30)->nullable()->after('total_payout_withdrawn');
            }
            if (!Schema::hasColumn('authors', 'payout_account_details')) {
                $table->text('payout_account_details')->nullable()->after('payout_account_type');
            }
        });

        // 2. Add KDP fields to ebooks table
        Schema::table('ebooks', function (Blueprint $table) {
            if (!Schema::hasColumn('ebooks', 'author_user_id')) {
                $table->foreignId('author_user_id')->nullable()->after('author_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('ebooks', 'royalty_percentage')) {
                $table->decimal('royalty_percentage', 5, 2)->default(50.00)->after('discount_price');
            }
            if (!Schema::hasColumn('ebooks', 'drm_enabled')) {
                $table->boolean('drm_enabled')->default(true)->after('royalty_percentage');
            }
            if (!Schema::hasColumn('ebooks', 'is_preorder')) {
                $table->boolean('is_preorder')->default(false)->after('drm_enabled');
            }
            if (!Schema::hasColumn('ebooks', 'preorder_release_date')) {
                $table->date('preorder_release_date')->nullable()->after('is_preorder');
            }
            if (!Schema::hasColumn('ebooks', 'preview_page_limit')) {
                $table->unsignedSmallInteger('preview_page_limit')->default(15)->after('preview_pages');
            }
        });

        // 3. Create author_royalties table
        if (!Schema::hasTable('author_royalties')) {
            Schema::create('author_royalties', function (Blueprint $table) {
                $table->id();
                $table->foreignId('author_id')->nullable()->constrained('authors')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('ebook_id')->constrained('ebooks')->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->decimal('sale_price', 10, 2);
                $table->decimal('royalty_percentage', 5, 2)->default(50.00);
                $table->decimal('royalty_amount', 10, 2);
                $table->decimal('platform_fee', 10, 2);
                $table->string('status', 30)->default('earned'); // earned, withdrawn, refunded
                $table->timestamps();

                $table->index(['author_id', 'status']);
                $table->index(['user_id', 'status']);
                $table->index('ebook_id');
            });
        }

        // 4. Create author_payout_requests table
        if (!Schema::hasTable('author_payout_requests')) {
            Schema::create('author_payout_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('author_id')->nullable()->constrained('authors')->nullOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('amount', 10, 2);
                $table->string('payment_method', 30); // bkash, nagad, rocket, bank
                $table->text('account_details');
                $table->decimal('tax_deduction_amount', 10, 2)->default(0.00);
                $table->decimal('net_payable_amount', 10, 2);
                $table->string('status', 30)->default('pending'); // pending, approved, paid, rejected
                $table->text('admin_notes')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->string('transaction_ref', 100)->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['author_id', 'status']);
            });
        }

        // 5. Create user_ebook_library table (DRM & Reading progress)
        if (!Schema::hasTable('user_ebook_library')) {
            Schema::create('user_ebook_library', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('ebook_id')->constrained('ebooks')->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->string('access_type', 30)->default('purchased'); // purchased, free, gift
                $table->unsignedInteger('last_read_page')->default(1);
                $table->unsignedTinyInteger('progress_percent')->default(0);
                $table->json('bookmarks_data')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['user_id', 'ebook_id']);
                $table->index('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_ebook_library');
        Schema::dropIfExists('author_payout_requests');
        Schema::dropIfExists('author_royalties');

        Schema::table('ebooks', function (Blueprint $table) {
            $table->dropColumn([
                'author_user_id',
                'royalty_percentage',
                'drm_enabled',
                'is_preorder',
                'preorder_release_date',
                'preview_page_limit',
            ]);
        });

        Schema::table('authors', function (Blueprint $table) {
            $table->dropColumn([
                'user_id',
                'royalty_percentage',
                'wallet_balance',
                'total_payout_withdrawn',
                'payout_account_type',
                'payout_account_details',
            ]);
        });
    }
};
