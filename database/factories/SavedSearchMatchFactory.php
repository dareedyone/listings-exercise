<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\SavedSearch;
use App\Models\SavedSearchMatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedSearchMatch>
 */
class SavedSearchMatchFactory extends Factory
{
    protected $model = SavedSearchMatch::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'saved_search_id' => SavedSearch::factory(),
            'listing_id' => Listing::factory()->live(),
        ];
    }
}
