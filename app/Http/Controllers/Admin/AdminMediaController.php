<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAccessService;
use App\Services\ImageOptimizerService;
use App\Support\SchemaCache;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

/**
 * /admin/media — the site's image library.
 *
 * Files are named by an opaque id ("s:books/covers/x.webp" encoded), never by a
 * server path, and every id must resolve to an image inside one of the library
 * folders below. Files the site still uses are protected from rename/move and
 * need an explicit confirmation to delete.
 */
class AdminMediaController extends Controller
{
    private const IMAGE_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico', 'bmp', 'avif'];
    private const RASTER_EXT = ['jpg', 'jpeg', 'png', 'webp'];
    private const PER_PAGE = 60;
    private const LIST_CACHE = 'media_library_v2';
    private const REF_CACHE = 'media_references_v2';
    private const LOOKUP_CACHE = 'media_asset_title_lookup_v3';
    private const DIM_CACHE = 'media_dimensions_map_v1';
    /** Folders whose every use is recorded in the database, so "Unused" is reliable there. */
    private const PURGEABLE = ['covers', 'look_inside', 'authors', 'publishers', 'ebooks', 'avatars'];

    public function __construct(private readonly ?AdminAccessService $accessService = null)
    {
    }

    // ------------------------------------------------------------------
    // Folders
    // ------------------------------------------------------------------

    /** Library folders: key => label, icon, colour, disk folders, upload folder, upload size. */
    private function folders(): array
    {
        $s = storage_path('app/public');
        $p = public_path('images');

        return [
            'covers'      => ['label' => 'Covers', 'icon' => 'fa-book', 'color' => '#059669', 'dirs' => [$s . '/books/covers', $p . '/books'], 'upload' => $s . '/books/covers', 'max' => 600],
            'look_inside' => ['label' => 'Pages', 'icon' => 'fa-book-open-reader', 'color' => '#d97706', 'dirs' => [$s . '/books/look_inside', $s . '/look_inside'], 'upload' => $s . '/books/look_inside', 'max' => 1000],
            'authors'     => ['label' => 'Authors', 'icon' => 'fa-user-pen', 'color' => '#7c3aed', 'dirs' => [$s . '/authors', $p . '/authors'], 'upload' => $s . '/authors', 'max' => 500],
            'publishers'  => ['label' => 'Publishers', 'icon' => 'fa-building', 'color' => '#0284c7', 'dirs' => [$s . '/publishers/logos', $s . '/publishers', $p . '/publishers'], 'upload' => $s . '/publishers/logos', 'max' => 500],
            'ebooks'      => ['label' => 'E-Books', 'icon' => 'fa-tablet-screen-button', 'color' => '#e11d48', 'dirs' => [$s . '/ebooks/covers', $s . '/ebooks'], 'upload' => $s . '/ebooks/covers', 'max' => 600],
            'banners'     => ['label' => 'Banners', 'icon' => 'fa-images', 'color' => '#0d9488', 'dirs' => [$p . '/banners', $s . '/campaigns', $s . '/banners'], 'upload' => $p . '/banners', 'max' => 1920],
            'blog'        => ['label' => 'Blog', 'icon' => 'fa-newspaper', 'color' => '#db2777', 'dirs' => [$s . '/blog', $p . '/blog'], 'upload' => $s . '/blog', 'max' => 1200],
            'avatars'     => ['label' => 'Avatars', 'icon' => 'fa-circle-user', 'color' => '#0891b2', 'dirs' => [$s . '/avatars'], 'upload' => $s . '/avatars', 'max' => 400],
            'payments'    => ['label' => 'Payments', 'icon' => 'fa-qrcode', 'color' => '#ea580c', 'dirs' => [$s . '/settings/qrcodes', $p . '/payments'], 'upload' => $s . '/settings/qrcodes', 'max' => 800],
            'settings'    => ['label' => 'Branding', 'icon' => 'fa-swatchbook', 'color' => '#475569', 'dirs' => [$p . '/settings', $s . '/settings'], 'upload' => $p . '/settings', 'max' => 1200],
            'signatures'  => ['label' => 'Signatures', 'icon' => 'fa-signature', 'color' => '#6d28d9', 'dirs' => [$s . '/signatures'], 'upload' => $s . '/signatures', 'max' => 800],
            'uploads'     => ['label' => 'Uploads', 'icon' => 'fa-cloud-arrow-up', 'color' => '#2563eb', 'dirs' => [$s . '/uploads', $s . '/images'], 'upload' => $s . '/uploads', 'max' => 1200],
        ];
    }

    /** Disk roots an id may point into. */
    private function roots(): array
    {
        return ['s' => storage_path('app/public'), 'p' => public_path('images')];
    }

    private static function norm(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }

    private function idFor(string $path): ?string
    {
        $path = self::norm($path);
        foreach ($this->roots() as $key => $root) {
            $root = self::norm($root) . '/';
            if (str_starts_with($path, $root)) {
                return rtrim(strtr(base64_encode($key . ':' . substr($path, strlen($root))), '+/', '-_'), '=');
            }
        }
        return null;
    }

    /** Library folder key that owns a file path (the first one listed wins). */
    private function folderOf(string $path): ?string
    {
        $path = self::norm($path);
        $best = null;
        $bestLen = -1;
        foreach ($this->folders() as $key => $f) {
            foreach ($f['dirs'] as $dir) {
                $dir = self::norm($dir) . '/';
                if (str_starts_with($path, $dir) && strlen($dir) > $bestLen) {
                    $best = $key;
                    $bestLen = strlen($dir);
                }
            }
        }
        return $best;
    }

    /**
     * Turn an id into a real file path, or null. The file must be an image
     * inside a library folder — never index.php, .htaccess, logs or backups.
     */
    private function resolve(?string $id): ?string
    {
        if (!is_string($id) || $id === '' || strlen($id) > 700) {
            return null;
        }
        $raw = base64_decode(strtr($id, '-_', '+/'), true);
        if ($raw === false || !preg_match('/^([sp]):(.+)$/s', $raw, $m)) {
            return null;
        }
        $rel = $m[2];
        if (str_contains($rel, "\0") || str_contains($rel, '..') || str_starts_with($rel, '/') || str_contains($rel, '\\') || preg_match('/^[a-z]:/i', $rel)) {
            return null;
        }
        $root = realpath($this->roots()[$m[1]]);
        $real = $root ? realpath($root . '/' . $rel) : false;
        if (!$real || !is_file($real) || !str_starts_with(self::norm($real), self::norm($root) . '/')) {
            return null;
        }
        if (!in_array(strtolower(pathinfo($real, PATHINFO_EXTENSION)), self::IMAGE_EXT, true) || !$this->folderOf($real)) {
            return null;
        }
        return self::norm($real);
    }

    /** @return array<string,string> id => path for the ids that resolve */
    private function resolveMany(array $ids): array
    {
        $out = [];
        foreach (array_slice(array_unique(array_filter($ids, 'is_string')), 0, 500) as $id) {
            if ($path = $this->resolve($id)) {
                $out[$id] = $path;
            }
        }
        return $out;
    }

    private function urlFor(string $path): string
    {
        $path = self::norm($path);
        $storage = self::norm(storage_path('app/public')) . '/';
        $rel = str_starts_with($path, $storage)
            ? 'storage/' . substr($path, strlen($storage))
            : ltrim(substr($path, strlen(self::norm(public_path()))), '/');

        return asset(implode('/', array_map('rawurlencode', explode('/', $rel))));
    }

    // ------------------------------------------------------------------
    // Pages
    // ------------------------------------------------------------------

    public function index(): View
    {
        $folders = collect($this->folders())->map(fn ($f, $k) => ['key' => $k, 'label' => $f['label'], 'icon' => $f['icon'], 'color' => $f['color'], 'max' => $f['max']])->values();

        return view('admin.media', [
            'folders' => $folders,
            'gd'      => extension_loaded('gd') && function_exists('imagewebp'),
            'zip'     => class_exists(ZipArchive::class),
        ]);
    }

    /** JSON list with filters, sort, paging and library stats. */
    public function items(Request $request): JsonResponse
    {
        $all = $this->library();
        $folder = (string) $request->query('folder', 'all');
        $format = (string) $request->query('format', 'all');
        $shape = (string) $request->query('shape', 'all');
        $usage = (string) $request->query('usage', 'all');
        $sort = (string) $request->query('sort', 'latest');
        $search = mb_strtolower(trim((string) $request->query('search', '')));

        $list = array_values(array_filter($all, function ($it) use ($folder, $format, $shape, $usage, $search) {
            if ($folder !== 'all' && $it['folder'] !== $folder) return false;
            if ($format !== 'all' && $it['format'] !== $format) return false;
            if ($usage === 'used' && $it['used'] !== true) return false;
            if ($usage === 'unused' && $it['used'] !== false) return false;
            if ($usage === 'heavy' && $it['size'] < 200 * 1024) return false;
            if ($shape !== 'all') {
                [$w, $h] = [$it['w'], $it['h']];
                if (!$w || !$h) return false;
                $ok = match ($shape) {
                    'wide'     => $w / $h >= 1.6,
                    'square'   => abs($w - $h) <= max(8, $w * 0.04),
                    'portrait' => $h > $w * 1.1,
                    'small'    => max($w, $h) <= 400,
                    default    => true,
                };
                if (!$ok) return false;
            }
            if ($search !== '' && !str_contains(mb_strtolower($it['name'] . ' ' . $it['title'] . ' ' . $it['sub']), $search)) return false;
            return true;
        }));

        usort($list, match ($sort) {
            'oldest'   => fn ($a, $b) => $a['t'] <=> $b['t'],
            'largest'  => fn ($a, $b) => $b['size'] <=> $a['size'],
            'smallest' => fn ($a, $b) => $a['size'] <=> $b['size'],
            'name'     => fn ($a, $b) => strnatcasecmp($a['name'], $b['name']),
            default    => fn ($a, $b) => $b['t'] <=> $a['t'],
        });

        if ($request->boolean('ids')) {
            // every matching id (for "select all" and step-by-step tools)
            return response()->json(['ids' => array_column($list, 'id'), 'count' => count($list)]);
        }

        $page = max(1, (int) $request->query('page', 1));
        $pages = max(1, (int) ceil(count($list) / self::PER_PAGE));
        $page = min($page, $pages);

        return response()->json([
            'items' => array_slice($list, ($page - 1) * self::PER_PAGE, self::PER_PAGE),
            'count' => count($list),
            'bytes' => array_sum(array_column($list, 'size')),
            'page'  => $page,
            'pages' => $pages,
            'stats' => $this->stats($all),
        ]);
    }

    private function stats(array $all): array
    {
        $folders = [];
        foreach (array_keys($this->folders()) as $k) {
            $folders[$k] = ['count' => 0, 'bytes' => 0];
        }
        $formats = [];
        $webp = $heavy = $unused = $raster = 0;
        foreach ($all as $it) {
            $folders[$it['folder']]['count']++;
            $folders[$it['folder']]['bytes'] += $it['size'];
            $formats[$it['format']] = ($formats[$it['format']] ?? 0) + 1;
            if ($it['format'] === 'webp') $webp++;
            if (in_array($it['format'], ['jpg', 'png', 'webp'], true)) $raster++;
            if ($it['size'] >= 200 * 1024) $heavy++;
            if ($it['used'] === false) $unused++;
        }
        arsort($formats);

        return [
            'count'   => count($all),
            'bytes'   => array_sum(array_column($all, 'size')),
            'webp'    => $webp,
            'webp_pct' => $raster ? (int) floor($webp * 100 / $raster) : 100,
            'heavy'   => $heavy,
            'unused'  => $unused,
            'folders' => $folders,
            'formats' => $formats,
        ];
    }

    /** Every image in the library (cached for a minute, cleared on each change). */
    private function library(): array
    {
        return Cache::remember(self::LIST_CACHE, 60, function () {
            $lookup = $this->titleLookup();
            $refs = $this->references();
            $dims = Cache::get(self::DIM_CACHE, []);
            $dims = is_array($dims) ? $dims : [];
            $dimsDirty = false;
            $seen = [];
            $items = [];

            foreach ($this->folders() as $key => $f) {
                foreach ($f['dirs'] as $dir) {
                    if (!File::isDirectory($dir)) {
                        continue;
                    }
                    foreach (File::allFiles($dir) as $file) {
                        $path = self::norm($file->getPathname());
                        if (isset($seen[$path]) || $this->folderOf($path) !== $key) {
                            continue;
                        }
                        $seen[$path] = true;
                        $ext = strtolower($file->getExtension());
                        if (!in_array($ext, self::IMAGE_EXT, true) || !($id = $this->idFor($path))) {
                            continue;
                        }

                        $mtime = $file->getMTime();
                        $dk = md5($path) . '_' . $mtime;
                        if (!isset($dims[$dk])) {
                            $info = in_array($ext, ['svg', 'ico'], true) ? false : @getimagesize($path);
                            $dims[$dk] = ['w' => $info[0] ?? null, 'h' => $info[1] ?? null];
                            $dimsDirty = true;
                        }

                        $name = $file->getFilename();
                        $base = pathinfo($name, PATHINFO_FILENAME);
                        $info = $lookup[$name] ?? $lookup[$base] ?? null;

                        $items[] = [
                            'id'     => $id,
                            'name'   => $name,
                            'folder' => $key,
                            'url'    => $this->urlFor($path) . '?v=' . $mtime,
                            'size'   => $file->getSize(),
                            'format' => $ext === 'jpeg' ? 'jpg' : $ext,
                            'w'      => $dims[$dk]['w'] ?? null,
                            'h'      => $dims[$dk]['h'] ?? null,
                            'title'  => $info['title'] ?? $this->prettyName($base),
                            'sub'    => $info['subtitle'] ?? $f['label'],
                            'link'   => $info['link'] ?? null,
                            // true = the site uses it, false = surely unused, null = cannot tell
                            'used'   => isset($refs[$name]) || isset($refs[$base]) ? true : (in_array($key, self::PURGEABLE, true) ? false : null),
                            't'      => $mtime,
                        ];
                    }
                }
            }

            if ($dimsDirty) {
                Cache::put(self::DIM_CACHE, array_slice($dims, -20000, null, true), 86400 * 7);
            }
            return $items;
        });
    }

    private function prettyName(string $base): string
    {
        $clean = trim(preg_replace('/[_-]+/', ' ', preg_replace('/[_-][0-9a-f]{8,}$/i', '', $base) ?? $base) ?? $base);
        return $clean !== '' && !preg_match('/^[0-9a-f]{16,}$/i', $clean) ? Str::headline($clean) : 'Image';
    }

    private function forget(): void
    {
        Cache::forget(self::LIST_CACHE);
        Cache::forget(self::REF_CACHE);
        Cache::forget(self::LOOKUP_CACHE);
    }

    /** Which record shows each file (books, authors, publishers, e-books, blog). */
    private function titleLookup(): array
    {
        return Cache::remember(self::LOOKUP_CACHE, 300, function () {
            $map = [];
            $add = function (?string $file, array $info) use (&$map) {
                $file = trim((string) $file);
                if ($file === '') return;
                $base = basename($file);
                $map[$base] = $info;
                $map[pathinfo($base, PATHINFO_FILENAME)] = $info;
            };

            try {
                if (SchemaCache::has('books')) {
                    foreach (DB::table('books')->get(['title', 'author_name', 'cover_image', 'look_inside_images', 'slug']) as $b) {
                        $link = url('/books/' . ($b->slug ?: $b->title));
                        $add($b->cover_image, ['title' => $b->title, 'subtitle' => 'Cover · ' . ($b->author_name ?: 'Idea'), 'link' => $link]);
                        foreach ($this->splitList($b->look_inside_images) as $img) {
                            $add($img, ['title' => $b->title, 'subtitle' => 'Inside page', 'link' => $link]);
                        }
                    }
                }
                foreach ([
                    ['authors', 'avatar', 'name', '/authors/', 'Author photo'],
                    ['publishers', 'logo', 'name', '/publishers/', 'Publisher logo'],
                    ['ebooks', 'cover_image', 'title', '/ebooks/', 'E-book cover'],
                    ['blog_posts', 'featured_image', 'title', '/blog/', 'Blog image'],
                ] as [$table, $col, $name, $prefix, $sub]) {
                    if (SchemaCache::has($table)) {
                        foreach (DB::table($table)->whereNotNull($col)->get([$name, $col, 'slug']) as $r) {
                            $add($r->{$col}, ['title' => $r->{$name}, 'subtitle' => $sub, 'link' => url($prefix . ($r->slug ?: $r->{$name}))]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Media title lookup failed: ' . $e->getMessage());
            }
            return $map;
        });
    }

    /** File names (with and without extension) that the database points to. */
    private function references(): array
    {
        return Cache::remember(self::REF_CACHE, 300, function () {
            $refs = [];
            $add = function ($value) use (&$refs) {
                foreach ($this->splitList($value) as $v) {
                    $base = basename(parse_url($v, PHP_URL_PATH) ?: $v);
                    if ($base !== '' && str_contains($base, '.')) {
                        $refs[$base] = true;
                        $refs[pathinfo($base, PATHINFO_FILENAME)] = true;
                    }
                }
            };

            foreach ($this->referenceColumns() as $table => $cols) {
                try {
                    if (!SchemaCache::has($table)) continue;
                    $cols = array_values(array_filter($cols, fn ($c) => \Illuminate\Support\Facades\Schema::hasColumn($table, $c)));
                    if (!$cols) continue;
                    foreach (DB::table($table)->get($cols) as $row) {
                        foreach ($cols as $c) {
                            $add($row->{$c});
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("Media references skipped {$table}: " . $e->getMessage());
                }
            }

            // settings hold paths inside text and JSON
            try {
                if (SchemaCache::has('site_settings')) {
                    foreach (DB::table('site_settings')->pluck('value') as $val) {
                        if (is_string($val) && preg_match_all('#[\w\-./%]+\.(?:webp|png|jpe?g|svg|gif|ico|avif|bmp)#i', $val, $m)) {
                            foreach ($m[0] as $hit) $add($hit);
                        }
                    }
                }
            } catch (\Throwable) {
            }
            return $refs;
        });
    }

    private function referenceColumns(): array
    {
        return [
            'books'           => ['cover_image', 'look_inside_images'],
            'authors'         => ['avatar'],
            'publishers'      => ['logo'],
            'ebooks'          => ['cover_image'],
            'blog_posts'      => ['featured_image'],
            'users'           => ['avatar'],
            'sliders'         => ['image', 'banner_image', 'cover_image'],
            'categories'      => ['image', 'icon', 'cover_image', 'banner'],
            'event_campaigns' => ['banner_image', 'card_bg_image', 'card_logo_image', 'card_event_logo_image', 'image'],
        ];
    }

    private function splitList($value): array
    {
        if ($value === null || $value === '') return [];
        $value = (string) $value;
        if (str_starts_with(trim($value), '[')) {
            $list = json_decode($value, true);
            return is_array($list) ? array_filter(array_map(fn ($v) => is_string($v) ? trim($v) : '', $list)) : [];
        }
        return array_filter(array_map('trim', explode(',', $value)));
    }

    private function isUsed(string $path): bool
    {
        $refs = $this->references();
        $name = basename($path);
        return isset($refs[$name]) || isset($refs[pathinfo($name, PATHINFO_FILENAME)]);
    }

    // ------------------------------------------------------------------
    // Changes
    // ------------------------------------------------------------------

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'files'     => 'required|array|min:1|max:30',
            'files.*'   => 'required|file|max:10240|mimes:jpeg,png,jpg,webp,svg,gif,ico,bmp,avif',
            'folder'    => ['required', Rule::in(array_keys($this->folders()))],
            'webp'      => 'nullable|boolean',
            'max'       => 'nullable|integer|in:0,400,600,800,1000,1200,1600,1920',
        ]);

        $folder = $this->folders()[$request->input('folder')];
        $dir = $folder['upload'];
        File::ensureDirectoryExists($dir, 0755);
        $webp = $request->boolean('webp', true) && function_exists('imagewebp');
        $max = $request->filled('max') ? (int) $request->input('max') : $folder['max'];

        $done = [];
        $failed = [];
        foreach ($request->file('files') as $file) {
            try {
                $done[] = $this->storeUpload($file, $dir, $webp, $max);
            } catch (\Throwable $e) {
                $failed[] = ['name' => $file->getClientOriginalName(), 'reason' => $e instanceof \RuntimeException ? $e->getMessage() : 'Could not save'];
                if (!$e instanceof \RuntimeException) Log::warning('Media upload failed: ' . $e->getMessage());
            }
        }

        $this->forget();
        $this->log('upload_media', 'Media: uploaded ' . count($done) . ' file(s) to ' . $folder['label']);

        return response()->json([
            'success' => count($done) > 0,
            'message' => count($done) . ' uploaded' . ($failed ? ', ' . count($failed) . ' failed' : ''),
            'items'   => $done,
            'failed'  => $failed,
        ], count($done) ? 200 : 422);
    }

    private function storeUpload(UploadedFile $file, string $dir, bool $webp, int $max): array
    {
        // the extension comes from the file's content, never from its name
        $ext = match ($file->guessExtension()) {
            'jpg', 'jpeg' => 'jpg', 'png' => 'png', 'webp' => 'webp', 'gif' => 'gif',
            'svg' => 'svg', 'ico' => 'ico', 'bmp' => 'bmp', 'avif' => 'avif', default => null,
        };
        if (!$ext) {
            throw new \RuntimeException('Not an image');
        }
        $raw = (string) file_get_contents($file->getRealPath());
        if ($ext === 'svg' && !$this->svgIsSafe($raw)) {
            throw new \RuntimeException('SVG has scripts or links — not allowed');
        }

        $hash = substr(hash('sha256', $raw), 0, 10);
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image';
        $base = Str::limit($base, 60, '');

        // same picture already here → reuse it
        foreach (File::files($dir) as $existing) {
            if (str_contains($existing->getFilename(), '_' . $hash . '.')) {
                return $this->itemFor(self::norm($existing->getPathname())) + ['reused' => true];
            }
        }

        $canEncode = in_array($ext, ['jpg', 'png', 'webp', 'bmp', 'avif'], true) && extension_loaded('gd');
        $target = $canEncode && $webp ? 'webp' : $ext;
        $dest = $dir . '/' . $base . '_' . $hash . '.' . $target;

        if ($canEncode) {
            $img = @imagecreatefromstring($raw);
            if (!$img) {
                throw new \RuntimeException('Image could not be read');
            }
            $this->writeImage($img, $dest, $target, $max);
            imagedestroy($img);
        } else {
            File::put($dest, $raw);
        }
        $this->mirror($dest);

        return $this->itemFor(self::norm($dest));
    }

    /**
     * Save a GD image, scaled down to fit $max (0 = keep size), with a size
     * goal that grows with the picture so banners stay sharp.
     */
    private function writeImage(\GdImage $img, string $dest, string $format, int $max, int $quality = 82): void
    {
        $w = imagesx($img);
        $h = imagesy($img);
        if ($max > 0 && max($w, $h) > $max) {
            $r = $max / max($w, $h);
            $nw = max(1, (int) round($w * $r));
            $nh = max(1, (int) round($h * $r));
            $scaled = imagecreatetruecolor($nw, $nh);
            imagealphablending($scaled, false);
            imagesavealpha($scaled, true);
            imagefill($scaled, 0, 0, imagecolorallocatealpha($scaled, 255, 255, 255, 127));
            imagecopyresampled($scaled, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
            $img = $scaled;
            $own = true;
            [$w, $h] = [$nw, $nh];
        } elseif ($format !== 'jpg') {
            imagealphablending($img, false);
            imagesavealpha($img, true);
        }

        $goal = (int) max(40 * 1024, min(400 * 1024, $w * $h * 0.18));
        $tmp = $dest . '.tmp';
        for ($q = $quality; ; $q -= 8) {
            match ($format) {
                'webp'  => imagewebp($img, $tmp, $q),
                'png'   => imagepng($img, $tmp, 9),
                default => $this->writeJpeg($img, $tmp, $q),
            };
            clearstatcache(true, $tmp);
            if ($format === 'png' || $q <= 60 || filesize($tmp) <= $goal) {
                break;
            }
        }
        File::move($tmp, $dest);
        if (!empty($own)) {
            imagedestroy($img);
        }
    }

    private function writeJpeg(\GdImage $img, string $file, int $q): void
    {
        // flatten transparency on white
        $bg = imagecreatetruecolor(imagesx($img), imagesy($img));
        imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
        imagecopy($bg, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
        imageinterlace($bg, true);
        imagejpeg($bg, $file, $q);
        imagedestroy($bg);
    }

    private function svgIsSafe(string $svg): bool
    {
        return str_contains($svg, '<svg')
            && !preg_match('/<\s*(script|foreignObject|iframe|embed|object|use[^>]+href\s*=\s*["\']?(?!#))|\son[a-z]+\s*=|javascript:|data:text\/html|<!ENTITY/i', $svg);
    }

    /** Saves the picture made in the editor, as a copy or over the original. */
    public function saveCustomized(Request $request): JsonResponse
    {
        $request->validate([
            'data_url' => 'required|string|max:20000000',
            'mode'     => 'required|in:overwrite,new_copy',
            'id'       => 'nullable|string|max:700',
            'name'     => 'nullable|string|max:100',
            'folder'   => ['nullable', Rule::in(array_keys($this->folders()))],
            'format'   => 'nullable|in:webp,png,jpg',
            'quality'  => 'nullable|integer|min:40|max:100',
        ]);

        if (!preg_match('#^data:image/(png|jpeg|webp);base64,#', $request->input('data_url'))) {
            return $this->fail('The picture data is not valid');
        }
        $raw = base64_decode(substr($request->input('data_url'), strpos($request->input('data_url'), ',') + 1), true);
        $img = $raw ? @imagecreatefromstring($raw) : false;
        if (!$img) {
            return $this->fail('The picture data is not valid');
        }
        $quality = (int) $request->input('quality', 85);

        if ($request->input('mode') === 'overwrite') {
            $path = $this->resolve($request->input('id'));
            $ext = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : null;
            if (!$path || !in_array($ext, self::RASTER_EXT, true)) {
                return $this->fail('Only JPG, PNG and WebP files can be overwritten — save a copy instead', 422);
            }
            // written in the file's own format so its link stays the same
            $this->writeImage($img, $path, $ext === 'jpeg' ? 'jpg' : $ext, 0, $quality);
            $dest = $path;
        } else {
            $folder = $this->folders()[$request->input('folder', 'uploads')];
            File::ensureDirectoryExists($folder['upload'], 0755);
            $format = $request->input('format', 'webp');
            $base = Str::limit(Str::slug((string) $request->input('name')) ?: 'edited', 60, '');
            $dest = $folder['upload'] . '/' . $base . '.' . $format;
            if (File::exists($dest)) {
                $dest = $folder['upload'] . '/' . $base . '_' . Str::lower(Str::random(5)) . '.' . $format;
            }
            $this->writeImage($img, $dest, $format, 0, $quality);
        }
        imagedestroy($img);
        $this->mirror($dest);
        $this->forget();
        $this->log('customize_media', 'Media: edited ' . basename($dest));

        return response()->json(['success' => true, 'message' => 'Saved ' . basename($dest), 'item' => $this->itemFor($dest)]);
    }

    public function renameFile(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|string|max:700', 'name' => 'required|string|max:100']);
        $path = $this->resolve($request->input('id'));
        if (!$path) {
            return $this->fail('File not found', 404);
        }
        if ($this->isUsed($path)) {
            return $this->fail('The site uses this file — renaming would break it. Use Replace instead.', 409);
        }

        $base = Str::limit(Str::slug(pathinfo($request->input('name'), PATHINFO_FILENAME)), 80, '');
        if ($base === '') {
            return $this->fail('Use English letters or numbers in the name');
        }
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $new = dirname($path) . '/' . $base . '.' . $ext;
        if ($new === $path) {
            return response()->json(['success' => true, 'message' => 'Name unchanged', 'item' => $this->itemFor($path)]);
        }
        if (File::exists($new)) {
            return $this->fail('A file with this name already exists here');
        }

        File::move($path, $new);
        $this->mirror($path, $new);
        $this->forget();
        $this->log('rename_media', 'Media: renamed ' . basename($path) . ' → ' . basename($new));

        return response()->json(['success' => true, 'message' => 'Renamed', 'item' => $this->itemFor($new)]);
    }

    /** delete | move | optimize | webp — on up to 500 files. */
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action'   => 'required|in:delete,move,optimize,webp',
            'ids'      => 'required|array|min:1|max:500',
            'ids.*'    => 'string|max:700',
            'folder'   => ['nullable', Rule::in(array_keys($this->folders()))],
            'force'    => 'nullable|boolean',
            'replace_original' => 'nullable|boolean',
        ]);

        $action = $request->input('action');
        $paths = $this->resolveMany($request->input('ids'));
        $done = $skipped = $saved = 0;
        $usedBlocked = [];
        $changed = [];

        if ($action === 'delete' && !$request->boolean('force')) {
            $inUse = array_filter($paths, fn ($p) => $this->isUsed($p));
            if ($inUse) {
                return response()->json([
                    'success' => false, 'needs_force' => true, 'in_use' => count($inUse),
                    'message' => count($inUse) . ' of these files are used on the site',
                ], 409);
            }
        }

        foreach ($paths as $id => $path) {
            $folderKey = $this->folderOf($path);
            switch ($action) {
                case 'delete':
                    File::delete($path);
                    $this->mirror($path, null);
                    $done++;
                    $changed[] = ['id' => $id, 'deleted' => true];
                    break;

                case 'move':
                    $target = $this->folders()[$request->input('folder')] ?? null;
                    if (!$target || $folderKey === $request->input('folder')) { $skipped++; break; }
                    if ($this->isUsed($path)) { $usedBlocked[] = basename($path); break; }
                    File::ensureDirectoryExists($target['upload'], 0755);
                    $dest = $target['upload'] . '/' . basename($path);
                    if (File::exists($dest)) { $skipped++; break; }
                    File::move($path, $dest);
                    $this->mirror($path, $dest);
                    $done++;
                    $changed[] = ['id' => $id, 'deleted' => true];
                    break;

                case 'optimize':
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    $before = filesize($path);
                    if ($ext === 'svg') {
                        $saved += ImageOptimizerService::optimizeSvgFile($path);
                    } elseif (in_array($ext, self::RASTER_EXT, true)) {
                        // already light and within the folder size → leave it (no quality loss from re-saving)
                        $dim = @getimagesize($path);
                        $max = $this->folders()[$folderKey]['max'] ?? 1200;
                        if (!$dim || (max($dim[0], $dim[1]) <= $max && $before <= max(40 * 1024, $dim[0] * $dim[1] * 0.18))) {
                            $skipped++;
                            break;
                        }
                        $img = @imagecreatefromstring((string) file_get_contents($path));
                        if (!$img) {
                            $skipped++;
                            break;
                        }
                        $tmp = $path . '.opt.' . $ext;
                        $this->writeImage($img, $tmp, $ext === 'jpeg' ? 'jpg' : $ext, $max);
                        imagedestroy($img);
                        clearstatcache(true, $tmp);
                        // keep the new file when it is lighter, or when an oversized picture was scaled down
                        if (filesize($tmp) < $before || max($dim[0], $dim[1]) > $max) {
                            File::move($tmp, $path);
                            clearstatcache(true, $path);
                            $saved += max(0, $before - filesize($path));
                        } else {
                            File::delete($tmp);
                            $skipped++;
                            break;
                        }
                    } else {
                        $skipped++;
                        break;
                    }
                    $this->mirror($path);
                    $done++;
                    $changed[] = ['id' => $id, 'item' => $this->itemFor($path)];
                    break;

                case 'webp':
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'bmp'], true) || !@getimagesize($path) || !function_exists('imagewebp')) { $skipped++; break; }
                    $img = @imagecreatefromstring((string) file_get_contents($path));
                    if (!$img) { $skipped++; break; }
                    $dest = preg_replace('/\.[^.\/]+$/', '.webp', $path);
                    if (File::exists($dest)) { $skipped++; imagedestroy($img); break; }
                    $before = filesize($path);
                    $this->writeImage($img, $dest, 'webp', 0, 85);
                    imagedestroy($img);
                    $this->mirror($dest);
                    $saved += max(0, $before - filesize($dest));
                    $done++;
                    if ($request->boolean('replace_original')) {
                        // point the database at the new file, then remove the old one
                        $this->repointReferences(basename($path), basename($dest));
                        File::delete($path);
                        $this->mirror($path, null);
                        $changed[] = ['id' => $id, 'deleted' => true];
                    }
                    $changed[] = ['id' => $this->idFor($dest), 'item' => $this->itemFor($dest)];
                    break;
            }
        }

        $this->forget();
        $msg = match ($action) {
            'delete'   => "Deleted {$done}",
            'move'     => "Moved {$done}" . ($usedBlocked ? ' · ' . count($usedBlocked) . ' in use, not moved' : ''),
            'optimize' => "Optimized {$done} · saved " . $this->formatBytes($saved),
            'webp'     => "Made {$done} WebP · saved " . $this->formatBytes($saved),
        } . ($skipped ? " · {$skipped} skipped" : '');
        $this->log('bulk_media_' . $action, 'Media: ' . $msg);

        return response()->json(['success' => true, 'message' => $msg, 'done' => $done, 'skipped' => $skipped, 'saved' => $saved, 'changed' => $changed]);
    }

    /** Swap old file names for the new one in the image columns of the database. */
    private function repointReferences(string $old, string $new): void
    {
        $oldBase = pathinfo($old, PATHINFO_FILENAME);
        foreach ($this->referenceColumns() + ['site_settings' => ['value']] as $table => $cols) {
            try {
                if (!SchemaCache::has($table)) continue;
                foreach ($cols as $col) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn($table, $col)) continue;
                    DB::table($table)->where($col, 'like', '%' . $old . '%')
                        ->get()->each(function ($row) use ($table, $col, $old, $new, $oldBase) {
                            $val = (string) $row->{$col};
                            $newVal = preg_replace('/(?<![\w\-])' . preg_quote($old, '/') . '(?![\w\-.])/u', $new, $val);
                            if ($newVal !== null && $newVal !== $val) {
                                $key = isset($row->id) ? 'id' : (isset($row->key) ? 'key' : null);
                                if ($key) DB::table($table)->where($key, $row->{$key})->update([$col => $newVal]);
                            }
                        });
                }
            } catch (\Throwable $e) {
                Log::warning("Media repoint skipped {$table}: " . $e->getMessage());
            }
        }
        try {
            \App\Support\SiteSetting::clearCache();
        } catch (\Throwable) {
        }
    }

    /** Puts a new picture in place of an old one; the link stays the same. */
    public function replaceFile(Request $request): JsonResponse
    {
        $request->validate([
            'id'   => 'required|string|max:700',
            'file' => 'required|file|max:10240|mimes:jpeg,png,jpg,webp,svg,gif,bmp,avif',
        ]);
        $path = $this->resolve($request->input('id'));
        if (!$path) {
            return $this->fail('File not found', 404);
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $raw = (string) file_get_contents($request->file('file')->getRealPath());
        $newExt = $request->file('file')->guessExtension();

        if (in_array($ext, self::RASTER_EXT, true)) {
            $img = @imagecreatefromstring($raw);
            if (!$img) {
                return $this->fail('The new picture could not be read');
            }
            $this->writeImage($img, $path, $ext === 'jpeg' ? 'jpg' : $ext, $this->folders()[$this->folderOf($path)]['max'] ?? 1200, 85);
            imagedestroy($img);
        } elseif ($ext === 'svg' && $newExt === 'svg') {
            if (!$this->svgIsSafe($raw)) {
                return $this->fail('SVG has scripts or links — not allowed');
            }
            File::put($path, $raw);
        } elseif ($newExt === $ext) {
            File::put($path, $raw);
        } else {
            return $this->fail('Upload a ' . strtoupper($ext) . ' file to replace this one');
        }

        $this->mirror($path);
        $this->forget();
        $this->log('replace_media', 'Media: replaced ' . basename($path));

        return response()->json(['success' => true, 'message' => 'Replaced — same link, new picture', 'item' => $this->itemFor($path)]);
    }

    public function downloadZip(Request $request): BinaryFileResponse|JsonResponse
    {
        $request->validate(['ids' => 'required|array|min:1|max:500', 'ids.*' => 'string|max:700']);
        if (!class_exists(ZipArchive::class)) {
            return $this->fail('ZIP is not available on this server', 500);
        }
        $paths = $this->resolveMany($request->input('ids'));
        if (!$paths) {
            return $this->fail('No files to download');
        }

        $zipPath = storage_path('app/media-' . Str::random(12) . '.zip');
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return $this->fail('Could not make the ZIP', 500);
        }
        $names = [];
        foreach ($paths as $path) {
            $name = basename($path);
            $n = 1;
            while (isset($names[$name])) {
                $name = pathinfo($path, PATHINFO_FILENAME) . '-' . (++$n) . '.' . pathinfo($path, PATHINFO_EXTENSION);
            }
            $names[$name] = true;
            $zip->addFile($path, $name);
        }
        $zip->close();

        return response()->download($zipPath, 'media-' . date('Y-m-d') . '.zip')->deleteFileAfterSend(true);
    }

    /** Files in the covered folders that nothing in the database uses (a day old or more). */
    public function unused(): JsonResponse
    {
        Cache::forget(self::REF_CACHE);
        Cache::forget(self::LIST_CACHE);
        $dayAgo = time() - 86400;
        $list = array_values(array_filter($this->library(), fn ($it) => $it['used'] === false && $it['t'] < $dayAgo));

        return response()->json([
            'items'   => array_slice($list, 0, 300),
            'count'   => count($list),
            'bytes'   => array_sum(array_column($list, 'size')),
            'folders' => array_map(fn ($k) => $this->folders()[$k]['label'], self::PURGEABLE),
        ]);
    }

    /** Redraws blog share cards drawn with the old design (broken Bengali), a few seconds at a time. */
    public function refreshCards(): JsonResponse
    {
        if (!class_exists(\Modules\Blog\Services\BlogPostOgImageService::class)) {
            return response()->json(['rebuilt' => 0, 'removed' => 0, 'remaining' => 0]);
        }
        try {
            $res = \Modules\Blog\Services\BlogPostOgImageService::rebuildStale(4.0);
        } catch (\Throwable $e) {
            Log::warning('Blog card rebuild failed: ' . $e->getMessage());
            return $this->fail('Could not redraw the blog cards', 500);
        }
        if ($res['rebuilt'] || $res['removed']) {
            $this->forget();
        }
        return response()->json($res);
    }
    public function destroy(Request $request): JsonResponse
    {
        $request->merge(['action' => 'delete', 'ids' => [(string) $request->input('id')]]);
        return $this->bulkAction($request);
    }

    // ------------------------------------------------------------------

    private function itemFor(string $path): array
    {
        $path = self::norm($path);
        $folder = $this->folderOf($path) ?? 'uploads';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        clearstatcache(true, $path);
        $dim = in_array($ext, ['svg', 'ico'], true) ? false : @getimagesize($path);
        $mtime = (int) @filemtime($path);

        return [
            'id'     => $this->idFor($path),
            'name'   => basename($path),
            'folder' => $folder,
            'url'    => $this->urlFor($path) . '?v=' . $mtime,
            'size'   => (int) @filesize($path),
            'format' => $ext === 'jpeg' ? 'jpg' : $ext,
            'w'      => $dim[0] ?? null,
            'h'      => $dim[1] ?? null,
            'title'  => $this->prettyName(pathinfo($path, PATHINFO_FILENAME)),
            'sub'    => $this->folders()[$folder]['label'],
            'link'   => null,
            'used'   => $this->isUsed($path) ? true : (in_array($folder, self::PURGEABLE, true) ? false : null),
            't'      => $mtime,
        ];
    }

    /**
     * Keep public/storage in step when the host copies files there instead of
     * using a link: $to = same path (write), new path (move) or null (delete).
     */
    private function mirror(string $from, string|null|false $to = false): void
    {
        $storage = self::norm(storage_path('app/public'));
        $public = self::norm(public_path('storage'));
        if (!is_dir($public) || is_link($public) || self::norm((string) realpath($public)) === self::norm((string) realpath($storage))) {
            return;
        }
        $map = fn (string $p) => str_starts_with(self::norm($p), $storage . '/') ? $public . substr(self::norm($p), strlen($storage)) : null;
        $pubFrom = $map($from);
        if (!$pubFrom) {
            return;
        }
        try {
            if ($to === false) {
                File::ensureDirectoryExists(dirname($pubFrom), 0755);
                File::copy($from, $pubFrom);
            } elseif ($to === null) {
                File::delete($pubFrom);
            } else {
                File::delete($pubFrom);
                if ($pubTo = $map($to)) {
                    File::ensureDirectoryExists(dirname($pubTo), 0755);
                    File::copy($to, $pubTo);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Media mirror failed: ' . $e->getMessage());
        }
    }

    private function fail(string $message, int $status = 422): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }

    private function log(string $action, string $message): void
    {
        try {
            $this->accessService?->log($action, $message);
        } catch (\Throwable) {
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $pow = $bytes > 0 ? min((int) floor(log($bytes, 1024)), 3) : 0;
        return round($bytes / (1024 ** $pow), $pow ? 1 : 0) . ' ' . $units[$pow];
    }
}
