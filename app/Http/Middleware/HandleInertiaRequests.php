<?php

namespace App\Http\Middleware;

use App\Models\UserNotification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $notifications = ['unreadCount' => 0, 'recent' => []];
        if ($request->user()) {
            $notifications['unreadCount'] = UserNotification::where('user_id', $request->user()->id)->whereNull('read_at')->count();
            $notifications['recent'] = UserNotification::where('user_id', $request->user()->id)
                ->orderByDesc('created_at')
                ->take(5)
                ->get()
                ->map(fn (UserNotification $n) => [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'message' => $n->message,
                    'read_at' => $n->read_at?->toIso8601String(),
                    'data' => $n->data,
                    'created_at' => $n->created_at->toIso8601String(),
                ]);
        }

        return array_merge(parent::share($request), [
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
            ],
            'notifications' => $notifications,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
                'status' => fn () => $request->session()->get('status'),
            ],
        ]);
    }
}
