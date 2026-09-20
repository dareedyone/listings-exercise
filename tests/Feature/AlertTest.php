<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\SavedSearch;
use App\Models\SavedSearchMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_their_saved_search_matches_as_alerts(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $listing = Listing::factory()->live()->create();

        $savedSearch = SavedSearch::factory()
            ->for($user)
            ->create();

        $otherSavedSearch = SavedSearch::factory()
            ->for($otherUser)
            ->create();

        $match = SavedSearchMatch::factory()->create([
            'saved_search_id' => $savedSearch->id,
            'listing_id' => $listing->id,
        ]);

        SavedSearchMatch::factory()->create([
            'saved_search_id' => $otherSavedSearch->id,
            'listing_id' => $listing->id,
        ]);

        $response = $this->get(route('alerts.index'));

        $response
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Alerts/Index')
                    ->has('alerts', 1)
                    ->where('alerts.0.listing.id', $listing->id)
                    ->has('alerts.0.saved_searches', 1)
                    ->where('alerts.0.saved_searches.0.id', $savedSearch->id)
            );
    }

    public function test_user_with_no_matches_sees_empty_alerts(): void
    {
        User::factory()->create();

        $response = $this->get(route('alerts.index'));

        $response
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Alerts/Index')
                    ->has('alerts', 0)
            );
    }

    public function test_matches_for_the_same_listing_are_grouped_into_one_alert(): void
    {
        $user = User::factory()->create();

        $listing = Listing::factory()->live()->create();

        $firstSavedSearch = SavedSearch::factory()
            ->for($user)
            ->create();

        $secondSavedSearch = SavedSearch::factory()
            ->for($user)
            ->create();

        SavedSearchMatch::factory()->create([
            'saved_search_id' => $firstSavedSearch->id,
            'listing_id' => $listing->id,
        ]);

        SavedSearchMatch::factory()->create([
            'saved_search_id' => $secondSavedSearch->id,
            'listing_id' => $listing->id,
        ]);

        $response = $this->get(route('alerts.index'));

        $response
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Alerts/Index')
                    ->has('alerts', 1)
                    ->where('alerts.0.listing.id', $listing->id)
                    ->has('alerts.0.saved_searches', 2)
            );
    }
}
