<?php

namespace Tests\Feature;

use App\Enums\PropertyType;
use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class SavedSearchManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_saved_search_creation_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/saved-searches/create')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('SavedSearches/Create')
                ->has('filters')
                ->has('branches')
                ->has('propertyTypes')
            );
    }

    public function test_creation_page_receives_existing_search_filters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/saved-searches/create?property_type=flat&region=Manchester&min_bedrooms=3&max_price=400000')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('SavedSearches/Create')
                ->where('filters.property_type', 'flat')
                ->where('filters.region', 'Manchester')
                ->where('filters.min_bedrooms', '3')
                ->where('filters.max_price', '400000')
            );
    }

    public function test_user_can_create_saved_search(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/saved-searches', [
                'property_type' => PropertyType::Flat->value,
                'region' => 'Manchester',
                'min_bedrooms' => 3,
                'max_price' => 400_000,
            ])
            ->assertRedirect(route('saved-searches.index'));

        $this->assertDatabaseHas('saved_searches', [
            'user_id' => $user->id,
            'property_type' => PropertyType::Flat->value,
            'region' => 'Manchester',
            'min_bedrooms' => 3,
            'max_price' => 400_000,
        ]);
    }

    public function test_user_can_view_their_saved_searches(): void
    {
        $user = User::factory()->create();

        SavedSearch::factory()->create([
            'user_id' => $user->id,
            'region' => 'Manchester',
        ]);

        $otherUser = User::factory()->create();

        SavedSearch::factory()->create([
            'user_id' => $otherUser->id,
            'region' => 'Leeds',
        ]);

        $this->actingAs($user)
            ->get('/saved-searches')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('SavedSearches/Index')
                ->has('savedSearches', 1)
                ->where('savedSearches.0.region', 'Manchester')
            );
    }

    public function test_user_can_delete_their_saved_search(): void
    {
        $user = User::factory()->create();

        $savedSearch = SavedSearch::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->delete("/saved-searches/{$savedSearch->id}")
            ->assertRedirect(route('saved-searches.index'));

        $this->assertDatabaseMissing('saved_searches', [
            'id' => $savedSearch->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_saved_search(): void
    {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        $savedSearch = SavedSearch::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $this->actingAs($user)
            ->delete("/saved-searches/{$savedSearch->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('saved_searches', [
            'id' => $savedSearch->id,
            'user_id' => $otherUser->id,
        ]);
    }

    public function test_invalid_saved_search_criteria_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/saved-searches', [
                'property_type' => 'invalid',
                'region' => str_repeat('a', 101),
                'min_bedrooms' => -1,
                'max_price' => -1,
            ])
            ->assertSessionHasErrors([
                'property_type',
                'region',
                'min_bedrooms',
                'max_price',
            ]);

        $this->assertDatabaseCount('saved_searches', 0);
    }

    public function test_user_id_is_always_taken_from_authenticated_user(): void
    {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        $this->actingAs($user)
            ->post('/saved-searches', [
                'user_id' => $otherUser->id,
                'region' => 'Manchester',
            ])
            ->assertRedirect(route('saved-searches.index'));

        $this->assertDatabaseHas('saved_searches', [
            'user_id' => $user->id,
            'region' => 'Manchester',
        ]);

        $this->assertDatabaseMissing('saved_searches', [
            'user_id' => $otherUser->id,
            'region' => 'Manchester',
        ]);
    }
}
