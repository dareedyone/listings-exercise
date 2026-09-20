<?php

namespace App\Models;

use App\Enums\ListingStatus;
use App\Enums\PropertyType;
use Database\Factories\ListingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * The casts() method tells Eloquent how to hydrate these attributes, but static
 * analysis can't infer that from a string map — these annotations do.
 *
 * @property int $id
 * @property int $branch_id
 * @property string $reference
 * @property string $address_line_1
 * @property string $city
 * @property string $postcode
 * @property int $price
 * @property int $bedrooms
 * @property int $bathrooms
 * @property PropertyType $property_type
 * @property ListingStatus $status
 * @property Carbon|null $listed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Branch $branch
 */
class Listing extends Model
{
    /** @use HasFactory<ListingFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'reference',
        'address_line_1',
        'city',
        'postcode',
        'price',
        'bedrooms',
        'bathrooms',
        'property_type',
        'status',
        'listed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'property_type' => PropertyType::class,
            'status' => ListingStatus::class,
            'listed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Only listings that are currently on the market.
     *
     * @param  Builder<Listing>  $query
     */
    public function scopeLive(Builder $query): void
    {
        $query->where('status', ListingStatus::Live);
    }

    /**
     * @return HasMany<SavedSearchMatch, $this>
     */
    public function savedSearchMatches(): HasMany
    {
        return $this->hasMany(SavedSearchMatch::class);
    }

    /**
     * @param  Builder<Listing>  $query
     * @param array{
     *     property_type?: mixed,
     *     max_price?: mixed,
     *     min_bedrooms?: mixed,
     *     region?: mixed,
     * } $filters
     */
    public function scopeMatching(
        Builder $query,
        array $filters,
    ): void {
        $query
            ->when(
                $filters['property_type'] ?? null,
                function (Builder $query, mixed $propertyType): void {
                    $query->where('property_type', $propertyType);
                },
            )
            ->when(
                $filters['max_price'] ?? null,
                function (Builder $query, mixed $maxPrice): void {
                    $query->where('price', '<=', $maxPrice);
                },
            )
            ->when(
                $filters['min_bedrooms'] ?? null,
                function (Builder $query, mixed $minBedrooms): void {
                    $query->where('bedrooms', '>=', $minBedrooms);
                },
            )
            ->when(
                $filters['region'] ?? null,
                function (Builder $query, mixed $region): void {
                    $query->whereHas(
                        'branch',
                        function (Builder $query) use ($region): void {
                            $query->where('region', $region);
                        },
                    );
                },
            );
    }
}
