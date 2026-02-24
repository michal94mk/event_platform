<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with('user:id,name', 'categories')
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

        $events = $query->get();
        $showingMine = $request->user() && ($request->user()->isOrganizer() || $request->user()->isAdmin()) && $request->boolean('mine');

        return Inertia::render('events/Index', [
            'events' => $events,
            'canCreate' => $request->user() && ($request->user()->isOrganizer() || $request->user()->isAdmin()),
            'showingMine' => $showingMine,
        ]);
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

        return Inertia::render('events/Show', [
            'event' => $event,
            'canUpdate' => $request->user() && $request->user()->can('update', $event),
            'canDelete' => $request->user() && $request->user()->can('delete', $event),
            'canRegister' => $canRegister,
            'placesLeft' => $placesLeft,
            'isOrganizer' => $request->user() && ($event->user_id === $request->user()->id || $request->user()->isAdmin()),
        ]);
    }

    public function edit(Event $event)
    {
        $this->authorize('update', $event);

        $event->load('categories');

        return Inertia::render('events/Edit', [
            'event' => $event,
            'categories' => EventCategory::orderBy('name')->get(['id', 'name', 'slug']),
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
