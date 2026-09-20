<?php

namespace App\Listeners;

use App\Events\ListingWentLive;
use App\Models\SavedSearch;

class CreateSavedSearchMatches
{
    public function handle(ListingWentLive $event): void
    {
        $listing = $event->listing;

        $savedSearches = SavedSearch::query()
            ->matchingListing($listing)
            ->get();

        foreach ($savedSearches as $savedSearch) {
            $savedSearch->matches()->firstOrCreate([
                'listing_id' => $listing->id,
            ]);
        }
    }
}
