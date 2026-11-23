<?php

namespace App\Http\Controllers;

use App\Support\Share;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Counts a share made from the share sheet (resources/views/components/share.blade.php). */
class ShareController extends Controller
{
    public function track(Request $request, string $type, string $key): JsonResponse
    {
        $data = $request->validate(['channel' => ['required', 'string', 'in:' . implode(',', Share::CHANNELS)]]);
        abort_unless(Share::valid($type, $key), 404);

        $visitor = hash('sha256', $request->ip() . '|' . substr((string) $request->userAgent(), 0, 120) . '|' . config('app.key'));
        $shares = Share::record($type, $key, $data['channel'], $request->user()?->id, $visitor);

        return response()->json(['success' => true, 'shares' => $shares]);
    }
}
