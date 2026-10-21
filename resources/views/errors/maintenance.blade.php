<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Back soon</title>
    <style>
        :root { color-scheme: light dark; --bg: #f6f8fb; --card: #fff; --ink: #0f172a; --muted: #64748b; --brand: #2563eb; }
        @media (prefers-color-scheme: dark) { :root { --bg: #0b1220; --card: #111a2c; --ink: #e6ecf5; --muted: #92a1b8; } }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: var(--bg); color: var(--ink); font-family: system-ui, -apple-system, "Segoe UI", "Hind Siliguri", sans-serif; }
        .box { max-width: 460px; width: 100%; text-align: center; background: var(--card); border-radius: 22px; padding: 38px 28px; box-shadow: 0 20px 50px -24px rgba(15, 23, 42, .35); }
        .ico { width: 64px; height: 64px; margin: 0 auto 18px; border-radius: 18px; display: grid; place-items: center; background: rgba(37, 99, 235, .12); color: var(--brand); font-size: 30px; }
        h1 { margin: 0 0 8px; font-size: 24px; }
        p { margin: 0; color: var(--muted); line-height: 1.7; white-space: pre-line; }
        .bar { height: 4px; border-radius: 99px; margin: 24px auto 0; width: 120px; background: linear-gradient(90deg, transparent, var(--brand), transparent); background-size: 200% 100%; animation: slide 1.6s linear infinite; }
        @keyframes slide { to { background-position: -200% 0; } }
    </style>
</head>
<body>
    <main class="box">
        <div class="ico" aria-hidden="true">&#9881;</div>
        <h1>We'll be back soon</h1>
        <p>{{ $reason !== '' ? $reason : 'The site is being updated. Please check back in a little while.' }}</p>
        <div class="bar"></div>
    </main>
</body>
</html>
