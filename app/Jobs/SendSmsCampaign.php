<?php

namespace App\Jobs;

use App\Services\SmsService;
use App\Support\CampaignLog;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

/**
 * An admin SMS campaign. Dispatched with dispatchAfterResponse(): the admin gets the page back at once and the
 * sending carries on in the same PHP process, so it needs no queue worker (the server may not run one).
 */
class SendSmsCampaign
{
    use Dispatchable;

    /**
     * @param string $mode    broadcast = one message to many numbers ($payload = numbers);
     *                        many = personal messages ($payload = [['to' => …, 'message' => …], …])
     */
    public function __construct(
        public string $mode,
        public array $payload,
        public string $message,
        public string $group,
        public string $title,
        public ?int $adminId,
        public string $adminName,
    ) {}

    public function handle(): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        ignore_user_abort(true);

        $total = count($this->payload);
        $type  = $this->mode === 'many' ? 'many_to_many' : 'broadcast';

        try {
            if ($this->mode === 'many') {
                $res     = SmsService::sendManyToMany($this->payload, ['source' => 'admin', 'campaign' => $this->title]);
                $sent    = (int) ($res['total_sent'] ?? 0);
                $refused = count($res['denied'] ?? []);
                $failed  = max(0, $total - $sent - $refused);
            } else {
                $res     = SmsService::send($this->payload, $this->message, ['source' => 'admin', 'purpose' => 'campaign', 'campaign' => $this->title]);
                $sent    = (int) ($res['sent'] ?? 0);
                $refused = count($res['denied'] ?? []);
                $failed  = (int) ($res['failed'] ?? 0);
            }
        } catch (\Throwable $e) {
            Log::error('SMS campaign crashed: ' . $e->getMessage());
            [$sent, $refused, $failed] = [0, 0, $total];
        }

        CampaignLog::record('sms', $this->group, $total, $sent, $failed + $refused, $this->title, $this->message, $type, $refused, $this->adminId, $this->adminName);
    }
}
