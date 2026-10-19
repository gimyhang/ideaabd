<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bengali speech for browsers that have no Bengali voice installed. The audiobook player
 * asks for one short sentence at a time; each result is cached on disk so a sentence is
 * only ever fetched from the speech provider once.
 */
class TtsController extends Controller
{
    public function bengali(Request $request): Response
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', (string) $request->query('q', '')));

        if ($text === '' || mb_strlen($text) > 200) {
            abort(422, 'Text must be 1-200 characters.');
        }

        $path = 'tts/bn/' . sha1($text) . '.mp3';
        $disk = Storage::disk('local');

        if (!$disk->exists($path)) {
            $res = Http::timeout(8)->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get('https://translate.google.com/translate_tts', ['ie' => 'UTF-8', 'client' => 'tw-ob', 'tl' => 'bn', 'q' => $text]);

            if (!$res->successful() || $res->body() === '') {
                abort(502, 'Speech service unavailable.');
            }
            $disk->put($path, $res->body());
        }

        return response($disk->get($path), 200, [
            'Content-Type'  => 'audio/mpeg',
            'Cache-Control' => 'public, max-age=2592000',
        ]);
    }
}
