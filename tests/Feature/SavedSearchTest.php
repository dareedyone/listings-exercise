<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Enums\PropertyType;
use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_saved_search_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $savedSearch = SavedSearch::factory()
            ->for($user)
            ->create();

        $this->assertTrue($savedSearch->user->is($user));
    }

    public function test_user_has_saved_searches(): void
    {
        $user = User::factory()->create();

        SavedSearch::factory()
            ->count(2)
            ->for($user)
            ->create();

        $this->assertCount(2, $user->savedSearches);
    }

    public function test_saved_search_can_store_a_region(): void
    {

        $savedSearch = SavedSearch::factory()

            ->forRegion('Manchester')

            ->create();

        $this->assertSame('Manchester', $savedSearch->region);
    }

    public function test_saved_search_criteria_are_cast_correctly(): void
    {
        $savedSearch = SavedSearch::factory()
            ->forRegion('Manchester')
            ->withPriceRange(300000, 500000)
            ->withBedrooms(2, 4)
            ->withPropertyType(PropertyType::Flat)
            ->live()
            ->create();

        $this->assertSame('Manchester', $savedSearch->region);
        $this->assertSame(300000, $savedSearch->min_price);
        $this->assertSame(500000, $savedSearch->max_price);

        $this->assertSame(2, $savedSearch->min_bedrooms);
        $this->assertSame(4, $savedSearch->max_bedrooms);

        $this->assertSame(PropertyType::Flat, $savedSearch->property_type);
        $this->assertSame(ListingStatus::Live, $savedSearch->status);
    }

    public function test_deleting_user_deletes_saved_searches(): void
    {
        $user = User::factory()->create();

        $savedSearch = SavedSearch::factory()
            ->for($user)
            ->create();

        $user->delete();

        $this->assertDatabaseMissing('saved_searches', [
            'id' => $savedSearch->id,
        ]);
    }
}
