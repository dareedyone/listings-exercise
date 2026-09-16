<?php

namespace Database\Seeders;

use App\Enums\PropertyType;
use App\Events\ListingWentLive;
use App\Models\Branch;
use App\Models\Listing;
use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // The demo user that the auth stub resolves every API request as.
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@street.example',
        ]);

        $branches = Branch::factory(8)->create();

        foreach ($branches as $branch) {
            Listing::factory(rand(18, 30))->live()->for($branch)->create();
            Listing::factory(rand(3, 6))->underOffer()->for($branch)->create();
            Listing::factory(rand(4, 8))->sold()->for($branch)->create();
            Listing::factory(rand(2, 5))->for($branch)->create(); // drafts
        }

        $demoBranch = $branches->first();

        SavedSearch::factory()
            ->for($user)
            ->create([
                'region' => $demoBranch->region,
                'property_type' => PropertyType::Flat,
                'min_bedrooms' => 2,
                'max_price' => 500000,
            ]);

        SavedSearch::factory()
            ->for($user)
            ->create([
                'region' => $demoBranch->region,
                'min_bedrooms' => 3,
            ]);

        $listing = Listing::factory()
            ->live()
            ->for($demoBranch)
            ->create([
                'property_type' => PropertyType::Flat,
                'bedrooms' => 3,
                'price' => 425000,
            ]);

        ListingWentLive::dispatch($listing);
    }
}
