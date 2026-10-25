<?php

namespace App\Support;

use App\Models\AdminDashboardSetting;
use Illuminate\Support\Facades\DB;

/**
 * The campaign history shown in the "হিস্ট্রি" tab (last 100 campaigns, kept as one JSON setting).
 */
class CampaignLog
{
    /** The row is locked while it is read-modified-written, so two campaigns finishing together cannot overwrite each other. */
    public static function record(string $channel, string $targetGroup, int $total, int $sent, int $failed, string $title, string $preview, string $type = 'broadcast', int $refused = 0, ?int $adminId = null, ?string $adminName = null): void
    {
        DB::transaction(function () use ($channel, $targetGroup, $total, $sent, $failed, $title, $preview, $type, $refused, $adminId, $adminName) {
            AdminDashboardSetting::firstOrCreate(['key' => 'messaging_campaign_logs'], ['value' => []]);
            $setting = AdminDashboardSetting::where('key', 'messaging_campaign_logs')->lockForUpdate()->first();
            $logs    = is_array($setting->value) ? $setting->value : [];

            array_unshift($logs, [
                'id'           => uniqid('camp_'),
                'channel'      => $channel,
                'type'         => $type,
                'target_group' => $targetGroup,
                'total'        => $total,
                'sent'         => $sent,
                'failed'       => $failed,
                'refused'      => $refused,
                'title'        => $title,
                'preview'      => mb_substr($preview, 0, 150),
                'created_at'   => now()->format('d M Y, h:i A'),
                'admin_name'   => $adminName ?? auth()->user()?->name ?? 'Admin',
            ]);

            $setting->value      = array_slice($logs, 0, 100);
            $setting->updated_by = $adminId ?? auth()->id();
            $setting->save();
        });
    }
}
