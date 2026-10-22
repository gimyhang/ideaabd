<?php

declare(strict_types=1);

namespace Modules\Book\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Book\Models\Book;
use Modules\Book\Models\BookShare;
use Modules\Book\Models\Wishlist;

/**
 * Small JSON endpoints behind the book page's share sheet and wishlist button.
 */
class BookEngagementController extends Controller
{
    /** Records that a visitor shared a book through a channel; one count per visitor, book and channel per hour. */
    public function share(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['channel' => ['required', 'string', 'in:' . implode(',', BookShare::CHANNELS)]]);
        $book = Book::query()->where('is_active', true)->findOrFail($id, ['id']);

        $visitor = hash('sha256', $request->ip() . '|' . substr((string) $request->userAgent(), 0, 120) . '|' . config('app.key'));

        $recent = BookShare::where('book_id', $book->id)->where('visitor_hash', $visitor)
            ->where('channel', $data['channel'])->where('created_at', '>=', now()->subHour())->exists();
        if (!$recent) {
            BookShare::create(['book_id' => $book->id, 'channel' => $data['channel'], 'user_id' => $request->user()?->id, 'visitor_hash' => $visitor]);
        }

        return response()->json(['success' => true, 'shares' => BookShare::where('book_id', $book->id)->count()]);
    }

    /** 1200x630 link-preview picture for Facebook / WhatsApp (see ShareCardService); falls back to the plain cover. */
    public function shareCard(string $slug, \Modules\Book\Services\ShareCardService $cards)
    {
        $book = Book::query()->where('is_active', true)->where(fn ($q) => $q->where('slug', $slug)->orWhere('id', ctype_digit($slug) ? (int) $slug : 0))->firstOrFail();
        $path = $cards->path($book);
        abort_unless($path, 404);

        return response()->file($path, ['Content-Type' => 'image/jpeg', 'Cache-Control' => 'public, max-age=86400']);
    }

    /** Adds or removes a book for the signed-in reader. Guests keep their list in the browser (see book-actions.js). */
    public function toggleWishlist(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'guest' => true, 'login_url' => route('login')], 401);
        }

        $book = Book::query()->where('is_active', true)->findOrFail($id, ['id']);
        $existing = Wishlist::where('user_id', $user->id)->where('book_id', $book->id)->first();

        if ($existing) {
            $existing->delete();
            $saved = false;
        } else {
            Wishlist::create(['user_id' => $user->id, 'book_id' => $book->id]);
            $saved = true;
        }

        return response()->json(['success' => true, 'saved' => $saved, 'count' => Wishlist::where('user_id', $user->id)->count()]);
    }

    /** After login: moves the books a guest saved in the browser into the account (union, never removes anything). */
    public function syncWishlist(Request $request): JsonResponse
    {
        $data = $request->validate(['ids' => ['required', 'array', 'max:100'], 'ids.*' => ['integer', 'min:1']]);
        $user = $request->user();

        $valid = Book::query()->where('is_active', true)->whereIn('id', $data['ids'])->pluck('id');
        foreach ($valid as $bookId) {
            Wishlist::firstOrCreate(['user_id' => $user->id, 'book_id' => $bookId]);
        }

        return response()->json(['success' => true, 'ids' => Wishlist::where('user_id', $user->id)->pluck('book_id')->all()]);
    }
}
