<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use App\Support\IpBlocklist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Visitors (admin/visitor-reports): traffic, sources, devices, live feed — and the security controls
 * (suspicious IPs, block / unblock). Visitor rows hold IP addresses, so the whole page is for the admin only.
 */
class VisitorReportController extends Controller
{
    public const PERIODS = ['today' => 'আজ', '7d' => '৭ দিন', '30d' => '৩০ দিন', '90d' => '৯০ দিন'];

    /** Search engines: shown, never suggested for blocking. */
    private const GOOD_BOTS = ['googlebot', 'bingbot', 'duckduckbot', 'yandexbot', 'applebot', 'baiduspider', 'facebookexternalhit', 'twitterbot', 'linkedinbot', 'whatsapp', 'telegrambot', 'slurp'];
    private const BAD_AGENT = ['bot', 'crawl', 'spider', 'scrap', 'curl', 'wget', 'python', 'java/', 'go-http', 'okhttp', 'headless', 'phantom', 'httpclient', 'libwww', 'axios', 'node-fetch', 'scrapy'];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->isAdmin(), 403, 'ভিজিটর রিপোর্ট ও নিরাপত্তা শুধু অ্যাডমিনের জন্য।');
            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        IpBlocklist::ensureSchema();
        $period = array_key_exists($request->input('period'), self::PERIODS) ? $request->input('period') : '7d';

        return view('admin.visitors.index', [
            'period'   => $period,
            'periods'  => self::PERIODS,
            'data'     => $this->report($period),
            'security' => $this->security($request),
            'myIp'     => $request->ip(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $period = array_key_exists($request->input('period'), self::PERIODS) ? $request->input('period') : '7d';

        return response()->json($this->report($period));
    }

    /** Live count + the newest visits (after an id, for the auto-refreshing feed) or a searched page of them. */
    public function live(Request $request): JsonResponse
    {
        $after = (int) $request->input('after', 0);
        $q = trim((string) $request->input('q', ''));
        $blocked = IpBlocklist::active();

        $rows = VisitorLog::query()
            ->when($after > 0 && $q === '', fn ($w) => $w->where('id', '>', $after))
            ->when($q !== '', function ($w) use ($q) {
                $like = '%' . $q . '%';
                $w->where(fn ($x) => $x->where('ip_address', 'like', $like)->orWhere('url', 'like', $like)->orWhere('page_title', 'like', $like)
                    ->orWhere('city', 'like', $like)->orWhere('country', 'like', $like)->orWhere('traffic_source', 'like', $like)->orWhere('device_name', 'like', $like));
            })
            ->with('user:id,name')
            ->latest('id')->limit($q !== '' ? 60 : 30)
            ->get(['id', 'ip_address', 'url', 'page_title', 'device', 'device_name', 'browser', 'os', 'country', 'country_code', 'city', 'traffic_source', 'user_id', 'visited_at', 'user_agent']);

        return response()->json([
            'live'  => VisitorLog::where('visited_at', '>=', now()->subMinutes(5))->distinct('ip_address')->count('ip_address'),
            'rows'  => $rows->map(fn ($r) => [
                'id'      => $r->id,
                'ip'      => $r->ip_address,
                'path'    => $this->path($r->url),
                'url'     => $r->url,
                'title'   => (string) $r->page_title,
                'device'  => $r->device ?: 'desktop',
                'client'  => trim(($r->browser ?: '') . ' · ' . ($r->os ?: ''), ' ·'),
                'place'   => trim(($r->city ? $r->city . ', ' : '') . ($r->country ?: ''), ', '),
                'flag'    => $this->flag($r->country_code),
                'source'  => $r->traffic_source ?: 'Direct',
                'user'    => $r->user?->name,
                'bot'     => $this->agentKind((string) $r->user_agent),
                'blocked' => isset($blocked[$r->ip_address]),
                'ago'     => optional($r->visited_at)->diffForHumans(null, true, true),
                'time'    => optional($r->visited_at)->format('d M, h:i:s A'),
            ])->values(),
        ]);
    }

    /** Everything one IP did (last 200 visits). */
    public function ip(Request $request): JsonResponse
    {
        $ip = (string) $request->input('ip');
        abort_unless(IpBlocklist::valid($ip), 422);
        $rows = VisitorLog::where('ip_address', $ip)->latest('id')->limit(200)->get(['url', 'page_title', 'visited_at', 'user_agent', 'country', 'city', 'device_name', 'browser', 'os']);
        $first = VisitorLog::where('ip_address', $ip)->min('visited_at');
        $blockedRow = IpBlocklist::all()->firstWhere('ip', $ip);

        return response()->json([
            'ip'      => $ip,
            'total'   => VisitorLog::where('ip_address', $ip)->count(),
            'day'     => VisitorLog::where('ip_address', $ip)->where('visited_at', '>=', now()->subDay())->count(),
            'first'   => $first ? Carbon::parse($first)->format('d M Y, h:i A') : null,
            'place'   => trim(($rows->first()?->city ? $rows->first()->city . ', ' : '') . ($rows->first()?->country ?? ''), ', '),
            'agent'   => (string) $rows->first()?->user_agent,
            'kind'    => $this->agentKind((string) $rows->first()?->user_agent),
            'blocked' => $blockedRow ? ['reason' => $blockedRow->reason, 'until' => $blockedRow->expires_at ? Carbon::parse($blockedRow->expires_at)->format('d M, h:i A') : null] : null,
            'mine'    => $ip === $request->ip(),
            'visits'  => $rows->map(fn ($r) => ['path' => $this->path($r->url), 'title' => (string) $r->page_title, 'time' => optional($r->visited_at)->format('d M, h:i:s A')]),
        ]);
    }

    public function block(Request $request): RedirectResponse|JsonResponse
    {
        $v = $request->validate([
            'ip'       => 'required|ip',
            'duration' => 'required|in:60,1440,10080,forever',
            'reason'   => 'nullable|string|max:200',
        ]);
        $ip = trim($v['ip']);
        // never lock the admin out
        if ($ip === $request->ip()) {
            return $this->answer($request, false, 'এটি আপনার নিজের IP — ব্লক করলে আপনি নিজেই ঢুকতে পারবেন না।');
        }
        $adminIp = VisitorLog::where('ip_address', $ip)->whereHas('user', fn ($q) => $q->whereIn('role', ['admin', 'sub_admin']))->exists();
        if ($adminIp && !$request->boolean('force')) {
            return $this->answer($request, false, 'এই IP থেকে অ্যাডমিন / কর্মকর্তা লগিন করেছেন — ব্লক করা হয়নি।');
        }

        $minutes = $v['duration'] === 'forever' ? null : (int) $v['duration'];
        IpBlocklist::block($ip, $minutes, $v['reason'] ?: 'অ্যাডমিন ব্লক করেছেন', 'manual', $request->user()->id);
        $label = ['60' => '১ ঘণ্টা', '1440' => '২৪ ঘণ্টা', '10080' => '৭ দিন', 'forever' => 'স্থায়ীভাবে'][$v['duration']];
        $this->audit('ip_blocked', "IP {$ip} ব্লক ({$label})");

        return $this->answer($request, true, "{$ip} ব্লক হয়েছে — {$label}।");
    }

    public function unblock(Request $request): RedirectResponse|JsonResponse
    {
        $v = $request->validate(['ip' => 'required|ip']);
        IpBlocklist::unblock($v['ip']);
        $this->audit('ip_unblocked', "IP {$v['ip']} আনব্লক");

        return $this->answer($request, true, "{$v['ip']} আবার ঢুকতে পারবে।");
    }

    // ── report data ──────────────────────────────────────────────────────────────────────────────────────

    private function range(string $period): array
    {
        $end = now();
        $start = match ($period) {
            'today' => now()->startOfDay(),
            '30d'   => now()->subDays(29)->startOfDay(),
            '90d'   => now()->subDays(89)->startOfDay(),
            default => now()->subDays(6)->startOfDay(),
        };
        $prevStart = $start->copy()->subSeconds($end->diffInSeconds($start));

        return [$start, $end, $prevStart];
    }

    public function report(string $period): array
    {
        return Cache::remember("visitors:report:v2:$period", 60, function () use ($period) {
            [$start, $end, $prevStart] = $this->range($period);
            $in = fn () => VisitorLog::query()->where('visited_at', '>=', $start);

            $kpi = $this->kpi($start, $end);
            $prev = $this->kpi($prevStart, $start);

            // trend: by hour for today, by day otherwise
            $hourly = $period === 'today';
            $bucket = $hourly ? 'SUBSTR(visited_at, 12, 2)' : 'SUBSTR(visited_at, 1, 10)';
            $raw = $in()->selectRaw("$bucket AS b, COUNT(*) AS v, COUNT(DISTINCT ip_address) AS u")->groupBy('b')->get()->keyBy('b');
            $labels = $views = $uniques = [];
            if ($hourly) {
                for ($h = 0; $h <= (int) now()->format('G'); $h++) {
                    $k = str_pad((string) $h, 2, '0', STR_PAD_LEFT);
                    $labels[] = Carbon::createFromTime($h)->format('g A');
                    $views[] = (int) ($raw[$k]->v ?? 0);
                    $uniques[] = (int) ($raw[$k]->u ?? 0);
                }
            } else {
                for ($d = $start->copy(); $d <= $end; $d->addDay()) {
                    $k = $d->format('Y-m-d');
                    $labels[] = $d->format('d M');
                    $views[] = (int) ($raw[$k]->v ?? 0);
                    $uniques[] = (int) ($raw[$k]->u ?? 0);
                }
            }

            // weekday × hour heat (Sat first, as the week starts in Bangladesh)
            $heat = array_fill(0, 7, array_fill(0, 24, 0));
            $heatFrom = $period === 'today' ? now()->subDays(6)->startOfDay() : $start;
            VisitorLog::where('visited_at', '>=', $heatFrom)
                ->selectRaw('SUBSTR(visited_at, 1, 10) AS d, SUBSTR(visited_at, 12, 2) AS h, COUNT(*) AS c')
                ->groupBy('d', 'h')->get()
                ->each(function ($r) use (&$heat) {
                    $dow = (Carbon::parse($r->d)->dayOfWeek + 1) % 7; // Sat=0 … Fri=6
                    $heat[$dow][(int) $r->h] += (int) $r->c;
                });

            $group = function (string $col, int $limit, string $fallback) use ($in) {
                return $in()->selectRaw("COALESCE(NULLIF($col, ''), ?) AS k, COUNT(*) AS c", [$fallback])
                    ->groupBy('k')->orderByDesc('c')->limit($limit)->get()
                    ->map(fn ($r) => ['label' => (string) $r->k, 'count' => (int) $r->c])->values()->all();
            };

            $countries = $in()->selectRaw("COALESCE(NULLIF(country, ''), 'অজানা') AS k, MAX(country_code) AS cc, COUNT(*) AS c")
                ->groupBy('k')->orderByDesc('c')->limit(10)->get()
                ->map(fn ($r) => ['label' => (string) $r->k, 'flag' => $this->flag($r->cc), 'count' => (int) $r->c])->values()->all();

            $pages = $in()->selectRaw('url, MAX(page_title) AS t, COUNT(*) AS c, COUNT(DISTINCT ip_address) AS u')
                ->groupBy('url')->orderByDesc('c')->limit(10)->get()
                ->map(fn ($r) => ['path' => $this->path($r->url), 'url' => $r->url, 'title' => (string) $r->t, 'count' => (int) $r->c, 'uniques' => (int) $r->u])->values()->all();

            $books = $in()->where('url', 'like', '%/books/%')->selectRaw('url, MAX(page_title) AS t, COUNT(*) AS c')
                ->groupBy('url')->orderByDesc('c')->limit(8)->get()
                ->map(fn ($r) => ['path' => $this->path($r->url), 'url' => $r->url, 'title' => trim(preg_replace('/\s*[|—-]\s*(আইডিয়া প্রকাশন|ideaabd).*$/u', '', (string) $r->t)) ?: $this->path($r->url), 'count' => (int) $r->c])->values()->all();

            return [
                'period'   => $period,
                'kpi'      => $kpi,
                'prev'     => $prev,
                'trend'    => ['labels' => $labels, 'views' => $views, 'uniques' => $uniques, 'hourly' => $hourly],
                'heat'     => $heat,
                'devices'  => $group('device', 5, 'desktop'),
                'channels' => $group('traffic_source', 8, 'Direct'),
                'browsers' => $group('browser', 6, 'অজানা'),
                'os'       => $group('os', 6, 'অজানা'),
                'models'   => $group('device_name', 6, 'অজানা'),
                'countries'=> $countries,
                'pages'    => $pages,
                'books'    => $books,
                'built_at' => now()->format('h:i A'),
            ];
        });
    }

    private function kpi(Carbon $from, Carbon $to): array
    {
        $r = VisitorLog::where('visited_at', '>=', $from)->where('visited_at', '<', $to)
            ->selectRaw("COUNT(*) AS v, COUNT(DISTINCT ip_address) AS u,
                COALESCE(SUM(CASE WHEN device = 'mobile' THEN 1 ELSE 0 END), 0) AS m,
                COUNT(DISTINCT user_id) AS members")->first();
        $v = (int) $r->v; $u = (int) $r->u;

        return [
            'views'   => $v,
            'uniques' => $u,
            'ppv'     => $u ? round($v / $u, 1) : 0,
            'mobile'  => $v ? round((int) $r->m / $v * 100) : 0,
            'members' => (int) $r->members,
        ];
    }

    // ── security ────────────────────────────────────────────────────────────────────────────────────────

    /** Busy or robot-like IPs of the last 24 hours, with the reason they stand out. */
    private function security(Request $request): array
    {
        $since = now()->subDay();
        $blocked = IpBlocklist::active();
        $rows = VisitorLog::where('visited_at', '>=', $since)
            ->selectRaw('ip_address, COUNT(*) AS hits, COUNT(DISTINCT url) AS pages, MIN(visited_at) AS first_at, MAX(visited_at) AS last_at, MAX(user_agent) AS ua, MAX(country) AS country, MAX(country_code) AS cc, MAX(user_id) AS uid')
            ->groupBy('ip_address')->orderByDesc('hits')->limit(200)->get();

        $suspects = [];
        $botHits = 0;
        foreach ($rows as $r) {
            $kind = $this->agentKind((string) $r->ua);
            $minutes = max(1, Carbon::parse($r->first_at)->diffInMinutes(Carbon::parse($r->last_at)));
            $rate = round($r->hits / $minutes, 1);
            $why = [];
            if ($kind === 'bad') { $why[] = 'স্ক্রিপ্ট / বট'; }
            if ($r->hits >= 300) { $why[] = number_format($r->hits) . ' বার / ২৪ ঘণ্টা'; }
            if ($rate >= 20 && $r->hits >= 40) { $why[] = "মিনিটে ~{$rate} পেজ"; }
            if ($r->pages >= 80) { $why[] = $r->pages . 'টি আলাদা পেজ'; }
            if ($kind !== 'human') { $botHits += $r->hits; }
            // members, search engines, the admin's own IP and local / private addresses are never suspects
            $public = filter_var($r->ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
            if ($why && $kind !== 'good' && !$r->uid && $public && $r->ip_address !== $request->ip()) {
                $suspects[] = [
                    'ip' => $r->ip_address, 'hits' => (int) $r->hits, 'pages' => (int) $r->pages, 'rate' => $rate,
                    'why' => $why, 'kind' => $kind, 'place' => (string) $r->country, 'flag' => $this->flag($r->cc),
                    'last' => Carbon::parse($r->last_at)->diffForHumans(), 'blocked' => isset($blocked[$r->ip_address]),
                    'score' => count($why) * 100 + min(99, (int) ($r->hits / 10)),
                ];
            }
        }
        usort($suspects, fn ($a, $b) => $b['score'] <=> $a['score']);

        $list = IpBlocklist::all();

        return [
            'suspects'  => array_slice($suspects, 0, 15),
            'blocked'   => $list,
            'bot_hits'  => $botHits,
            'ips_24h'   => $rows->count(),
            'auto'      => $list->where('source', 'auto')->count(),
            'guard_on'  => (bool) config('security.enabled', true),
            'limits'    => ['per_min' => (int) config('security.global_per_minute', 240), 'strikes' => (int) config('security.strikes_to_block', 4), 'block_min' => (int) config('security.block_minutes', 60)],
        ];
    }

    /** human | good (search engine) | bad (script, scraper, unknown bot) */
    private function agentKind(string $ua): string
    {
        $ua = strtolower($ua);
        if ($ua === '') {
            return 'bad';
        }
        foreach (self::GOOD_BOTS as $g) {
            if (str_contains($ua, $g)) {
                return 'good';
            }
        }
        foreach (self::BAD_AGENT as $b) {
            if (str_contains($ua, $b)) {
                return 'bad';
            }
        }

        return 'human';
    }

    private function path(?string $url): string
    {
        $p = (string) parse_url((string) $url, PHP_URL_PATH);
        $q = (string) parse_url((string) $url, PHP_URL_QUERY);

        return rawurldecode(($p ?: '/') . ($q ? '?' . $q : ''));
    }

    private function flag(?string $cc): string
    {
        $cc = strtoupper((string) $cc);
        if (strlen($cc) !== 2 || !ctype_alpha($cc)) {
            return '🌐';
        }

        return mb_chr(ord($cc[0]) + 127397, 'UTF-8') . mb_chr(ord($cc[1]) + 127397, 'UTF-8');
    }

    private function answer(Request $request, bool $ok, string $msg): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $msg], $ok ? 200 : 422);
        }

        return back()->with($ok ? 'success' : 'error', $msg);
    }

    private function audit(string $action, string $text): void
    {
        try {
            app(\App\Services\AdminAccessService::class)->log($action, $text);
        } catch (\Throwable) {
        }
    }
}
