<?php

/**
 * Abuse protection used by App\Http\Middleware\AbuseGuard. Every value can be changed in .env without touching code.
 */
return [
    'enabled' => env('ABUSE_GUARD', true),

    // Requests per minute for one IP over the whole site (signed-in users get the second number).
    'global_per_minute'  => (int) env('ABUSE_GLOBAL_PER_MINUTE', 240),
    'member_per_minute'  => (int) env('ABUSE_MEMBER_PER_MINUTE', 600),

    // Scanners and exploit kits: the User-Agent contains one of these words → 403.
    'blocked_agents' => [
        'sqlmap', 'nikto', 'nmap', 'masscan', 'acunetix', 'nessus', 'openvas', 'havij', 'dirbuster', 'gobuster', 'wpscan',
        'zgrab', 'nuclei', 'w3af', 'arachni', 'jaeles', 'httpx', 'fuzz faster', 'ffuf', 'burp', 'metasploit', 'hydra',
    ],

    // Aggressive scrapers that give nothing back. Search engines (Google, Bing, DuckDuckGo…) are never listed here.
    'blocked_scrapers' => [
        'ahrefsbot', 'semrushbot', 'mj12bot', 'dotbot', 'blexbot', 'petalbot', 'bytespider', 'dataforseobot', 'serpstatbot',
        'seekportbot', 'megaindex', 'zoominfobot', 'barkrowler', 'linkpadbot', 'awariobot', 'imagesiftbot',
    ],

    // Probing of paths that do not exist on a Laravel site: each hit is a strike.
    'probe_paths' => '#^/?(wp-(admin|login|content|includes|json)|xmlrpc\.php|\.env|\.git|\.svn|\.aws|\.ssh|phpmyadmin|pma|myadmin|adminer|cgi-bin|administrator|admin\.php|setup\.php|install\.php|shell|c99|r57|eval-stdin|vendor/phpunit|config\.(php|json|yml)|backup\.(zip|sql|tar)|.*\.(sql|bak|old|swp|orig)$)#i',
    'strikes_to_block'   => (int) env('ABUSE_STRIKES', 4),
    'strike_window_min'  => 10,
    'block_minutes'      => (int) env('ABUSE_BLOCK_MINUTES', 60),

    // A real person needs a moment to fill in a form; a script posts instantly.
    'min_form_seconds' => 2,
];
