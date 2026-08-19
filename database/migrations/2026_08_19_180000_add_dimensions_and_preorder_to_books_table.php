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
        Schema::table('books', function (Blueprint $table) {
            if (!Schema::hasColumn('books', 'book_size')) {
                $table->string('book_size', 100)->nullable()->after('paper_type')
                      ->comment('বইয়ের মাপ / সাইজ (যেমন: ডিমাই ১/৮, রয়্যাল, ৫.৫" × ৮.৫", A5)');
            }
            if (!Schema::hasColumn('books', 'pre_order_release_date')) {
                $table->date('pre_order_release_date')->nullable()->after('stock_status')
                      ->comment('প্রি-অর্ডার বইয়ের সম্ভাব্য প্রকাশ / সরবরাহের তারিখ');
            }
            if (!Schema::hasColumn('books', 'pre_order_note')) {
                $table->text('pre_order_note')->nullable()->after('pre_order_release_date')
                      ->comment('প্রি-অর্ডারের বিশেষ বার্তা / অফার বা গিফট সংক্রান্ত নোট');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $columns = [
                'book_size',
                'pre_order_release_date',
                'pre_order_note',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('books', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
