<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Modules\SEO\Models\SeoMeta;
use Modules\SEO\Services\AutoSeoScannerService;

Route::get('/seo/meta', function (Request $request) {
    $path = '/' . ltrim($request->input('path', '/'), '/');
    $seo = SeoMeta::where('url_path', $path)->first();
    
    if (!$seo) {
        $seo = app(AutoSeoScannerService::class)->scanAndSavePath($path);
    }
    
    return response()->json([
        'success' => true,
        'data'    => $seo,
    ]);
});
