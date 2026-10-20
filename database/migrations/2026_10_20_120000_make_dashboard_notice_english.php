<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD = 'স্বাগতম নতুন মাল্টিফাংশনাল অ্যাডমিন ড্যাশবোর্ডে!';

    /** Replaces the untouched Bangla welcome notice with one English word; a notice an admin edited is left alone. */
    public function up(): void
    {
        if (!Schema::hasTable('admin_dashboard_settings')) {
            return;
        }

        $row = DB::table('admin_dashboard_settings')->where('key', 'system_notice')->first();
        $value = $row ? json_decode((string) $row->value, true) : null;

        if (is_array($value) && ($value['text'] ?? null) === self::OLD) {
            $value['text'] = 'Welcome';
            DB::table('admin_dashboard_settings')->where('key', 'system_notice')->update(['value' => json_encode($value, JSON_UNESCAPED_UNICODE), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        $row = DB::table('admin_dashboard_settings')->where('key', 'system_notice')->first();
        $value = $row ? json_decode((string) $row->value, true) : null;

        if (is_array($value) && ($value['text'] ?? null) === 'Welcome') {
            $value['text'] = self::OLD;
            DB::table('admin_dashboard_settings')->where('key', 'system_notice')->update(['value' => json_encode($value, JSON_UNESCAPED_UNICODE), 'updated_at' => now()]);
        }
    }
};
