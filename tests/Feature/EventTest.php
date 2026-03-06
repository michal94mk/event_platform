<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Index
    // -----------------------------------------------------------------------

    public function test_index_shows_only_published_events_to_guests(): void
    {
        Event::factory()->published()->count(2)->create();
        Event::factory()->draft()->create();

        $this->get(route('events.index'))->assertOk();
    }

    public function test_index_returns_filters_and_categories(): void
    {
        $this->get(route('events.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('filters')
                ->has('categories')
            );
    }

    public function test_organizer_mine_filter_returns_only_their_events(): void
    {
        $organizer = User::factory()->organizer()->create();
        Event::factory()->published()->create(['user_id' => $organizer->id]);
        Event::factory()->draft()->create(['user_id' => $organizer->id]);
        Event::factory()->published()->create(); // innego organizatora

        $this->actingAs($organizer)
            ->get(route('events.index', ['mine' => 1]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('events.total', 2)
                ->where('showingMine', true)
            );
    }

    public function test_index_filters_by_search_query(): void
    {
        Event::factory()->published()->create(['title' => 'Laravel Conference 2026']);
        Event::factory()->published()->create(['title' => 'Niezwiązane wydarzenie']);

        $this->get(route('events.index', ['search' => 'Laravel']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('events.total', 1));
    }

    public function test_index_filters_by_city(): void
    {
        Event::factory()->published()->create(['venue_city' => 'Warszawa']);
        Event::factory()->published()->create(['venue_city' => 'Kraków']);

        $this->get(route('events.index', ['city' => 'Warszawa']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('events.total', 1));
    }

    public function test_index_filters_by_category(): void
    {
        $category = EventCategory::factory()->create();
        $eventWithCategory = Event::factory()->published()->create();
        $eventWithCategory->categories()->attach($category);
        Event::factory()->published()->create();

        $this->get(route('events.index', ['category' => $category->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('events.total', 1));
    }

    public function test_index_filters_free_events(): void
    {
        Event::factory()->published()->create(['ticket_price' => null]);
        Event::factory()->published()->paid(100)->create();

        $this->get(route('events.index', ['price' => 'free']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('events.total', 1));
    }

    public function test_index_filters_paid_events(): void
    {
        Event::factory()->published()->create(['ticket_price' => null]);
        Event::factory()->published()->paid(50)->create();

        $this->get(route('events.index', ['price' => 'paid']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('events.total', 1));
    }

    // -----------------------------------------------------------------------
    // Create
    // -----------------------------------------------------------------------

    public function test_guests_are_redirected_from_create_to_login(): void
    {
        $this->get(route('events.create'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_attendee_cannot_access_create_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('events.create'))
            ->assertForbidden();
    }

    public function test_organizer_can_access_create_page(): void
    {
        $organizer = User::factory()->organizer()->create();

        $this->actingAs($organizer)
            ->get(route('events.create'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('categories'));
    }

    public function test_admin_can_access_create_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('events.create'))
            ->assertOk();
    }

    // -----------------------------------------------------------------------
    // Store
    // -----------------------------------------------------------------------

    public function test_organizer_can_create_an_event(): void
    {
        $organizer = User::factory()->organizer()->create();

        $this->actingAs($organizer)
            ->post(route('events.store'), $this->validEventData())
            ->assertRedirect();

        $this->assertDatabaseHas('events', [
            'title'   => 'Testowe Wydarzenie Laravel',
            'user_id' => $organizer->id,
            'status'  => 'draft',
        ]);
    }

    public function test_event_slug_is_generated_from_title(): void
    {
        $organizer = User::factory()->organizer()->create();

        $this->actingAs($organizer)
            ->post(route('events.store'), $this->validEventData(['title' => 'Mój Super Event']));

        $this->assertDatabaseHas('events', ['slug' => 'moj-super-event']);
    }

    public function test_duplicate_slug_gets_numeric_suffix(): void
    {
        $organizer = User::factory()->organizer()->create();
        Event::factory()->create(['slug' => 'duplikat-slug', 'user_id' => $organizer->id]);

        $this->actingAs($organizer)
            ->post(route('events.store'), $this->validEventData(['title' => 'Duplikat Slug']));

        $this->assertDatabaseHas('events', ['slug' => 'duplikat-slug-1']);
    }

    public function test_new_event_status_is_draft(): void
    {
        $organizer = User::factory()->organizer()->create();

        $this->actingAs($organizer)
            ->post(route('events.store'), $this->validEventData());

        $event = Event::where('title', 'Testowe Wydarzenie Laravel')->firstOrFail();
        $this->assertSame('draft', $event->status);
    }

    public function test_store_attaches_categories_to_event(): void
    {
        $organizer = User::factory()->organizer()->create();
        $category = EventCategory::factory()->create();

        $this->actingAs($organizer)
            ->post(route('events.store'), $this->validEventData(['category_ids' => [$category->id]]));

        $event = Event::where('title', 'Testowe Wydarzenie Laravel')->firstOrFail();
        $this->assertCount(1, $event->categories);
    }

    public function test_store_saves_cover_image(): void
    {
        Storage::fake('public');
        $organizer = User::factory()->organizer()->create();

        $this->actingAs($organizer)
            ->post(route('events.store'), $this->validEventData([
                'cover_image' => UploadedFile::fake()->image('cover.jpg'),
            ]));

        $event = Event::where('title', 'Testowe Wydarzenie Laravel')->firstOrFail();
        $this->assertNotNull($event->cover_image);
        Storage::disk('public')->assertExists($event->cover_image);
    }

    public function test_regular_user_cannot_store_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('events.store'), $this->validEventData())
            ->assertForbidden();
    }

    public function test_store_validates_required_fields(): void
    {
        $organizer = User::factory()->organizer()->create();

        $this->actingAs($organizer)
            ->post(route('events.store'), [])
            ->assertInvalid(['title', 'description', 'start_date', 'end_date', 'venue_name']);
    }

    // -----------------------------------------------------------------------
    // Show
    // -----------------------------------------------------------------------

    public function test_anyone_can_view_published_event(): void
    {
        $event = Event::factory()->published()->create();

        $this->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('event'));
    }

    public function test_guest_cannot_view_draft_event(): void
    {
        $event = Event::factory()->draft()->create();

        $this->get(route('events.show', $event->slug))
            ->assertForbidden();
    }

    public function test_owner_can_view_their_draft_event(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->draft()->create(['user_id' => $organizer->id]);

        $this->actingAs($organizer)
            ->get(route('events.show', $event->slug))
            ->assertOk();
    }

    public function test_admin_can_view_any_draft_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->draft()->create();

        $this->actingAs($admin)
            ->get(route('events.show', $event->slug))
            ->assertOk();
    }

    public function test_non_owner_cannot_view_draft_event(): void
    {
        $event = Event::factory()->draft()->create();
        $other = User::factory()->create();

        $this->actingAs($other)
            ->get(route('events.show', $event->slug))
            ->assertForbidden();
    }

    public function test_can_register_is_false_for_past_events(): void
    {
        $event = Event::factory()->published()->past()->create();

        $this->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('canRegister', false));
    }

    public function test_can_register_is_true_for_upcoming_published_event(): void
    {
        $event = Event::factory()->published()->create();

        $this->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('canRegister', true));
    }

    // -----------------------------------------------------------------------
    // Edit
    // -----------------------------------------------------------------------

    public function test_guests_are_redirected_from_edit_to_login(): void
    {
        $event = Event::factory()->create();

        $this->get(route('events.edit', $event->slug))
            ->assertRedirect(route('login'));
    }

    public function test_owner_can_access_edit_page(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['user_id' => $organizer->id]);

        $this->actingAs($organizer)
            ->get(route('events.edit', $event->slug))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('event')->has('categories'));
    }

    public function test_non_owner_cannot_access_edit_page(): void
    {
        $event = Event::factory()->create();
        $other = User::factory()->organizer()->create();

        $this->actingAs($other)
            ->get(route('events.edit', $event->slug))
            ->assertForbidden();
    }

    public function test_admin_can_access_edit_page_of_any_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)
            ->get(route('events.edit', $event->slug))
            ->assertOk();
    }

    // -----------------------------------------------------------------------
    // Update
    // -----------------------------------------------------------------------

    public function test_owner_can_update_event(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['user_id' => $organizer->id]);

        $this->actingAs($organizer)
            ->put(route('events.update', $event->slug), $this->validUpdateData())
            ->assertRedirect(route('events.show', $event->slug));

        $this->assertDatabaseHas('events', [
            'id'    => $event->id,
            'title' => 'Zaktualizowany Tytuł',
        ]);
    }

    public function test_admin_can_update_any_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)
            ->put(route('events.update', $event->slug), $this->validUpdateData())
            ->assertRedirect();
    }

    public function test_non_owner_cannot_update_event(): void
    {
        $event = Event::factory()->create();
        $other = User::factory()->organizer()->create();

        $this->actingAs($other)
            ->put(route('events.update', $event->slug), $this->validUpdateData())
            ->assertForbidden();
    }

    public function test_owner_can_change_event_status(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->draft()->create(['user_id' => $organizer->id]);

        $this->actingAs($organizer)
            ->put(route('events.update', $event->slug), $this->validUpdateData(['status' => 'published']));

        $this->assertDatabaseHas('events', ['id' => $event->id, 'status' => 'published']);
    }

    // -----------------------------------------------------------------------
    // Destroy
    // -----------------------------------------------------------------------

    public function test_owner_can_delete_event(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['user_id' => $organizer->id]);

        $this->actingAs($organizer)
            ->delete(route('events.destroy', $event->slug))
            ->assertRedirect(route('events.index'));

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_admin_can_delete_any_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)
            ->delete(route('events.destroy', $event->slug))
            ->assertRedirect(route('events.index'));
    }

    public function test_non_owner_cannot_delete_event(): void
    {
        $event = Event::factory()->create();
        $other = User::factory()->organizer()->create();

        $this->actingAs($other)
            ->delete(route('events.destroy', $event->slug))
            ->assertForbidden();

        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function test_guest_cannot_delete_event(): void
    {
        $event = Event::factory()->create();

        $this->delete(route('events.destroy', $event->slug))
            ->assertRedirect(route('login'));
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function validEventData(array $overrides = []): array
    {
        return array_merge([
            'title'         => 'Testowe Wydarzenie Laravel',
            'description'   => 'Opis testowego wydarzenia, który jest wystarczająco długi.',
            'start_date'    => now()->addDays(10)->format('Y-m-d H:i:s'),
            'end_date'      => now()->addDays(10)->addHours(4)->format('Y-m-d H:i:s'),
            'venue_name'    => 'Sala Konferencyjna A',
            'venue_city'    => 'Warszawa',
            'venue_country' => 'Poland',
        ], $overrides);
    }

    private function validUpdateData(array $overrides = []): array
    {
        return array_merge([
            'title'         => 'Zaktualizowany Tytuł',
            'description'   => 'Zaktualizowany opis wydarzenia.',
            'start_date'    => now()->addDays(14)->format('Y-m-d H:i:s'),
            'end_date'      => now()->addDays(14)->addHours(4)->format('Y-m-d H:i:s'),
            'venue_name'    => 'Nowa Sala B',
            'venue_city'    => 'Kraków',
            'venue_country' => 'Poland',
        ], $overrides);
    }
}
