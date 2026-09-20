<?php

namespace Database\Factories;

use App\Enums\ListingStatus;
use App\Enums\PropertyType;
use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedSearch>
 */
class SavedSearchFactory extends Factory
{
    protected $model = SavedSearch::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'region' => null,
            'city' => null,
            'postcode' => null,
            'min_price' => null,
            'max_price' => null,
            'min_bedrooms' => null,
            'max_bedrooms' => null,
            'min_bathrooms' => null,
            'max_bathrooms' => null,
            'property_type' => null,
            'status' => null,
        ];
    }

    public function forRegion(string $region): static
    {

        return $this->state([

            'region' => $region,

        ]);
    }

    public function withPriceRange(int $min, int $max): static
    {
        return $this->state(fn () => [
            'min_price' => $min,
            'max_price' => $max,
        ]);
    }

    public function withBedrooms(int $min, ?int $max = null): static
    {
        return $this->state(fn () => [
            'min_bedrooms' => $min,
            'max_bedrooms' => $max,
        ]);
    }

    public function withPropertyType(PropertyType $propertyType): static
    {
        return $this->state(fn () => [
            'property_type' => $propertyType,
        ]);
    }

    public function live(): static
    {
        return $this->state(fn () => [
            'status' => ListingStatus::Live,
        ]);
    }
}
