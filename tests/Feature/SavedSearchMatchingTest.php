<?php

namespace Tests\Feature;

use App\Enums\PropertyType;
use App\Events\ListingWentLive;
use App\Models\Branch;
use App\Models\Listing;
use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SavedSearchMatchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_creates_match_for_matching_saved_search(): void
    {
        $user = User::factory()->create();

        $branch = Branch::factory()->create([
            'region' => 'Manchester',
        ]);

        $savedSearch = SavedSearch::factory()
            ->for($user)
            ->create([
                'region' => 'Manchester',
                'property_type' => PropertyType::Flat,
                'min_bedrooms' => 2,
                'max_price' => 500000,
            ]);

        $listing = Listing::factory()
            ->for($branch)
            ->live()
            ->create([
                'property_type' => PropertyType::Flat,
                'bedrooms' => 3,
                'price' => 450000,
            ]);

        ListingWentLive::dispatch($listing);

        $this->assertDatabaseHas('saved_search_matches', [
            'saved_search_id' => $savedSearch->id,
            'listing_id' => $listing->id,
        ]);
    }

    public function test_listing_does_not_match_when_criteria_do_not_match(): void
    {
        $user = User::factory()->create();

        $branch = Branch::factory()->create([
            'region' => 'Manchester',
        ]);

        SavedSearch::factory()
            ->for($user)
            ->create([
                'region' => 'Leeds',
            ]);

        SavedSearch::factory()
            ->for($user)
            ->create([
                'property_type' => PropertyType::Flat,
            ]);

        SavedSearch::factory()
            ->for($user)
            ->create([
                'min_bedrooms' => 5,
            ]);

        SavedSearch::factory()
            ->for($user)
            ->create([
                'max_price' => 400000,
            ]);

        $listing = Listing::factory()
            ->for($branch)
            ->live()
            ->create([
                'property_type' => PropertyType::Detached,
                'bedrooms' => 3,
                'price' => 450000,
            ]);

        ListingWentLive::dispatch($listing);

        $this->assertDatabaseCount('saved_search_matches', 0);
    }

    public function test_null_criteria_match_any_listing_value(): void
    {
        $user = User::factory()->create();

        $savedSearch = SavedSearch::factory()
            ->for($user)
            ->create();

        $listing = Listing::factory()->live()->create();

        ListingWentLive::dispatch($listing);

        $this->assertDatabaseHas('saved_search_matches', [
            'saved_search_id' => $savedSearch->id,
            'listing_id' => $listing->id,
        ]);
    }

    public function test_listing_can_match_multiple_saved_searches(): void
    {
        $user = User::factory()->create();

        $branch = Branch::factory()->create([
            'region' => 'Manchester',
        ]);

        $firstSearch = SavedSearch::factory()
            ->for($user)
            ->create([
                'region' => 'Manchester',
                'max_price' => 500000,
            ]);

        $secondSearch = SavedSearch::factory()
            ->for($user)
            ->create([
                'region' => 'Manchester',
                'min_bedrooms' => 2,
            ]);

        $listing = Listing::factory()
            ->for($branch)
            ->live()
            ->create([
                'bedrooms' => 3,
                'price' => 450000,
            ]);

        ListingWentLive::dispatch($listing);

        $this->assertDatabaseHas('saved_search_matches', [
            'saved_search_id' => $firstSearch->id,
            'listing_id' => $listing->id,
        ]);

        $this->assertDatabaseHas('saved_search_matches', [
            'saved_search_id' => $secondSearch->id,
            'listing_id' => $listing->id,
        ]);
    }

    public function test_listing_is_not_matched_twice_for_same_saved_search(): void
    {
        $user = User::factory()->create();

        $savedSearch = SavedSearch::factory()
            ->for($user)
            ->create();

        $listing = Listing::factory()->live()->create();

        ListingWentLive::dispatch($listing);
        ListingWentLive::dispatch($listing);

        $this->assertDatabaseCount('saved_search_matches', 1);

        $this->assertDatabaseHas('saved_search_matches', [
            'saved_search_id' => $savedSearch->id,
            'listing_id' => $listing->id,
        ]);
    }

    /**
     * @return array{0: Listing, 1: SavedSearch}
     */
    private function listingAndSavedSearch(): array
    {
        $branch = Branch::factory()->create([
            'region' => 'Manchester',
        ]);

        $listing = Listing::factory()
            ->for($branch)
            ->live()
            ->create([
                'property_type' => PropertyType::Flat,
                'bedrooms' => 3,
                'price' => 450_000,
            ]);

        $savedSearch = SavedSearch::factory()->create([
            'region' => 'Manchester',
            'property_type' => PropertyType::Flat,
            'min_bedrooms' => 2,
            'max_price' => 500_000,
        ]);

        return [$listing, $savedSearch];
    }

    public function test_saved_search_matching_agrees_with_listing_search(): void
    {
        [$listing, $savedSearch] = $this->listingAndSavedSearch();

        $this->assertTrue(
            Listing::query()
                ->whereKey($listing->id)
                ->matching($savedSearch->criteria())
                ->exists(),
        );

        $this->assertTrue(
            SavedSearch::query()
                ->whereKey($savedSearch->id)
                ->matchingListing($listing)
                ->exists(),
        );
    }

    #[DataProvider('mismatchedCriteria')]
    public function test_saved_search_matching_rejects_the_same_listings_as_listing_search(
        array $criteria,
    ): void {
        [$listing] = $this->listingAndSavedSearch();

        $savedSearch = SavedSearch::factory()->create($criteria);

        $this->assertFalse(
            Listing::query()
                ->whereKey($listing->id)
                ->matching($savedSearch->criteria())
                ->exists(),
        );

        $this->assertFalse(
            SavedSearch::query()
                ->whereKey($savedSearch->id)
                ->matchingListing($listing)
                ->exists(),
        );
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function mismatchedCriteria(): array
    {
        return [
            'region' => [['region' => 'Leeds']],
            'property type' => [['property_type' => PropertyType::Detached]],
            'min bedrooms' => [['min_bedrooms' => 5]],
            'max price' => [['max_price' => 400_000]],
        ];
    }
}
