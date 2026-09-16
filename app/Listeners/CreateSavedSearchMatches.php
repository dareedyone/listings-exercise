<?php

namespace App\Listeners;

use App\Events\ListingWentLive;
use App\Models\SavedSearch;

class CreateSavedSearchMatches
{
    public function handle(ListingWentLive $event): void
    {
        $listing = $event->listing->loadMissing('branch');

        $savedSearches = SavedSearch::query()
            ->where(function ($query) use ($listing) {
                $query
                    ->whereNull('property_type')
                    ->orWhere('property_type', $listing->property_type->value);
            })
            ->where(function ($query) use ($listing) {
                $query
                    ->whereNull('region')
                    ->orWhere('region', $listing->branch->region);
            })
            ->where(function ($query) use ($listing) {
                $query
                    ->whereNull('min_bedrooms')
                    ->orWhere('min_bedrooms', '<=', $listing->bedrooms);
            })
            ->where(function ($query) use ($listing) {
                $query
                    ->whereNull('max_price')
                    ->orWhere('max_price', '>=', $listing->price);
            })
            ->get();

        foreach ($savedSearches as $savedSearch) {
            $savedSearch->matches()->firstOrCreate([
                'listing_id' => $listing->id,
            ]);
        }
    }
}
