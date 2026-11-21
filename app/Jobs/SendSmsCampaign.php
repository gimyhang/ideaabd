<?php

namespace App\Jobs;

use App\Services\SmsCampaignRunner;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Starts sending a saved SMS campaign right after the admin gets the page back (dispatchAfterResponse, no queue
 * worker needed). It sends for a limited time only; if the campaign is bigger, the admin page (while open) and the
 * scheduler continue it from the saved position — so a server that stops long PHP runs no longer cuts it short.
 */
class SendSmsCampaign
{
    use Dispatchable;

    public function __construct(public int $campaignId, public int $seconds = 40) {}

    public function handle(): void
    {
        SmsCampaignRunner::work($this->campaignId, $this->seconds);
    }
}
