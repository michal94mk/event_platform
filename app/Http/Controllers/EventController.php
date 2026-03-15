<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with('user:id,name', 'categories');

        if ($request->user()) {
            if ($request->user()->isOrganizer() || $request->user()->isAdmin()) {
                if ($request->boolean('mine')) {
                    $query->where('user_id', $request->user()->id);
                } else {
                    $query->where('status', 'published');
                }
            } else {
                $query->where('status', 'published');
            }
        } else {
            $query->where('status', 'published');
        }

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('venue_name', 'like', "%{$search}%")
                    ->orWhere('venue_city', 'like', "%{$search}%");
            });
        }

        if ($city = $request->string('city')->toString()) {
            $query->where('venue_city', 'like', "%{$city}%");
        }

        if ($category = $request->string('category')->toString()) {
            $query->whereHas('categories', fn ($q) => $q->where('slug', $category));
        }

        if ($price = $request->string('price')->toString()) {
            if ($price === 'free') {
                $query->where(function ($q) {
                    $q->whereNull('ticket_price')
                        ->orWhere('ticket_price', 0);
                });
            } elseif ($price === 'paid') {
                $query->where('ticket_price', '>', 0);
            }
        }

        $sort = $request->string('sort')->toString();
        switch ($sort) {
            case 'date_desc':
                $query->orderByDesc('start_date');
                break;
            case 'title_asc':
                $query->orderBy('title');
                break;
            case 'title_desc':
                $query->orderByDesc('title');
                break;
            default:
                $query->orderBy('start_date');
                break;
        }

        $events = $query->paginate(12)->withQueryString();
        $showingMine = $request->user() && ($request->user()->isOrganizer() || $request->user()->isAdmin()) && $request->boolean('mine');

        $filters = $request->only(['search', 'city', 'category', 'price', 'sort']);
        $view = $request->string('view')->toString() ?: 'list';
        if (! in_array($view, ['list', 'calendar'], true)) {
            $view = 'list';
        }

        return Inertia::render('events/Index', [
            'events' => $events,
            'canCreate' => $request->user() && ($request->user()->isOrganizer() || $request->user()->isAdmin()),
            'showingMine' => $showingMine,
            'filters' => $filters,
            'categories' => EventCategory::orderBy('name')->get(['id', 'name', 'slug']),
            'view' => $view,
        ]);
    }

    public function calendar(Request $request)
    {
        $start = $request->date('start') ?? now()->startOfMonth();
        $end = $request->date('end') ?? now()->endOfMonth();

        $query = Event::query()
            ->select('id', 'title', 'slug', 'start_date', 'end_date', 'venue_name', 'venue_city')
            ->whereBetween('start_date', [$start, $end])
            ->orderBy('start_date');

        if ($request->user()) {
            if ($request->user()->isOrganizer() || $request->user()->isAdmin()) {
                if ($request->boolean('mine')) {
                    $query->where('user_id', $request->user()->id);
                } else {
                    $query->where('status', 'published');
                }
            } else {
                $query->where('status', 'published');
            }
        } else {
            $query->where('status', 'published');
        }

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('venue_name', 'like', "%{$search}%")
                    ->orWhere('venue_city', 'like', "%{$search}%");
            });
        }

        if ($city = $request->string('city')->toString()) {
            $query->where('venue_city', 'like', "%{$city}%");
        }

        if ($category = $request->string('category')->toString()) {
            $query->whereHas('categories', fn ($q) => $q->where('slug', $category));
        }

        if ($price = $request->string('price')->toString()) {
            if ($price === 'free') {
                $query->where(function ($q) {
                    $q->whereNull('ticket_price')->orWhere('ticket_price', 0);
                });
            } elseif ($price === 'paid') {
                $query->where('ticket_price', '>', 0);
            }
        }

        $events = $query->get();

        return response()->json(
            $events->map(fn ($event) => [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date->toIso8601String(),
                'end' => ($event->end_date ?? $event->start_date)->toIso8601String(),
                'url' => route('events.show', $event->slug),
                'extendedProps' => [
                    'venue' => trim(implode(', ', array_filter([$event->venue_name, $event->venue_city]))),
                ],
            ])
        );
    }

    public function create()
    {
        $this->authorize('create', Event::class);

        return Inertia::render('events/Create', [
            'categories' => EventCategory::orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function store(StoreEventRequest $request)
    {
        $slug = Str::slug($request->title);
        $original = $slug;
        $i = 1;
        while (Event::where('slug', $slug)->exists()) {
            $slug = $original.'-'.$i++;
        }

        $event = Event::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'slug' => $slug,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'venue_name' => $request->venue_name,
            'venue_address' => $request->venue_address,
            'venue_city' => $request->venue_city,
            'venue_country' => $request->venue_country,
            'venue_latitude' => $request->venue_latitude,
            'venue_longitude' => $request->venue_longitude,
            'max_attendees' => $request->max_attendees,
            'ticket_price' => $request->ticket_price ?: null,
            'currency' => $request->currency ?? 'PLN',
            'status' => 'draft',
        ]);

        if ($request->filled('category_ids')) {
            $event->categories()->sync($request->category_ids);
        }

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('event-covers', 'public');
            $event->update(['cover_image' => $path]);
        }

        return redirect()->route('events.show', $event->slug)
            ->with('success', 'Wydarzenie zostało utworzone.');
    }

    public function show(Request $request, Event $event)
    {
        $this->authorize('view', $event);

        $event->load('user:id,name', 'categories');

        $registrationsCount = $event->registrations()->count();
        $placesLeft = $event->max_attendees ? max(0, $event->max_attendees - $registrationsCount) : null;
        $canRegister = $event->status === 'published'
            && ($placesLeft === null || $placesLeft > 0)
            && $event->start_date->isFuture();

        $registerDisabledReason = null;
        if ($event->status === 'published' && ! $canRegister) {
            if ($event->start_date->isPast()) {
                $registerDisabledReason = 'Rejestracja zamknięta – wydarzenie już się odbyło.';
            } elseif ($placesLeft !== null && $placesLeft <= 0) {
                $registerDisabledReason = 'Rejestracja zamknięta – brak wolnych miejsc.';
            }
        } elseif ($event->status !== 'published') {
            $registerDisabledReason = 'Rejestracja niedostępna – wydarzenie nie jest jeszcze opublikowane.';
        }

        return Inertia::render('events/Show', [
            'event' => $event,
            'canUpdate' => $request->user() && $request->user()->can('update', $event),
            'canDelete' => $request->user() && $request->user()->can('delete', $event),
            'canRegister' => $canRegister,
            'registerDisabledReason' => $registerDisabledReason,
            'placesLeft' => $placesLeft,
            'isOrganizer' => $request->user() && ($event->user_id === $request->user()->id || $request->user()->isAdmin()),
        ]);
    }

    public function edit(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $event->load('categories');

        $hasGoogleCalendar = Integration::where('user_id', $request->user()->id)
            ->where('provider', Integration::PROVIDER_GOOGLE_CALENDAR)
            ->where('is_active', true)
            ->exists();

        return Inertia::render('events/Edit', [
            'event' => $event,
            'categories' => EventCategory::orderBy('name')->get(['id', 'name', 'slug']),
            'hasGoogleCalendar' => $hasGoogleCalendar,
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        $data = $request->only([
            'title', 'description', 'start_date', 'end_date',
            'venue_name', 'venue_address', 'venue_city', 'venue_country',
            'venue_latitude', 'venue_longitude', 'max_attendees',
            'ticket_price', 'currency', 'status',
        ]);

        if (isset($data['ticket_price']) && $data['ticket_price'] === '') {
            $data['ticket_price'] = null;
        }
        if (isset($data['status']) === false) {
            unset($data['status']);
        }

        $event->update($data);

        if (array_key_exists('category_ids', $request->all())) {
            $event->categories()->sync($request->category_ids ?? []);
        }

        if ($request->hasFile('cover_image')) {
            if ($event->cover_image && Storage::disk('public')->exists($event->cover_image)) {
                Storage::disk('public')->delete($event->cover_image);
            }
            $path = $request->file('cover_image')->store('event-covers', 'public');
            $event->update(['cover_image' => $path]);
        }

        return redirect()->route('events.show', $event->slug)
            ->with('success', 'Wydarzenie zostało zaktualizowane.');
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Wydarzenie zostało usunięte.');
    }
}
