<?php

namespace App\Models;

use App\Enums\ListingStatus;
use App\Enums\PropertyType;
use Database\Factories\SavedSearchFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $region
 * @property string|null $city
 * @property string|null $postcode
 * @property int|null $min_price
 * @property int|null $max_price
 * @property int|null $min_bedrooms
 * @property int|null $max_bedrooms
 * @property int|null $min_bathrooms
 * @property int|null $max_bathrooms
 * @property PropertyType|null $property_type
 * @property ListingStatus|null $status
 * @property-read User $user
 */
class SavedSearch extends Model
{
    /** @use HasFactory<SavedSearchFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'region',
        'city',
        'postcode',
        'min_price',
        'max_price',
        'min_bedrooms',
        'max_bedrooms',
        'min_bathrooms',
        'max_bathrooms',
        'property_type',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_price' => 'integer',
            'max_price' => 'integer',
            'min_bedrooms' => 'integer',
            'max_bedrooms' => 'integer',
            'min_bathrooms' => 'integer',
            'max_bathrooms' => 'integer',
            'property_type' => PropertyType::class,
            'status' => ListingStatus::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<SavedSearchMatch, $this>
     */
    public function matches(): HasMany
    {
        return $this->hasMany(SavedSearchMatch::class);
    }

    /**
     * @return array{
     *     property_type: string|null,
     *     region: string|null,
     *     min_bedrooms: int|null,
     *     max_price: int|null,
     * }
     */
    public function criteria(): array
    {
        return [
            'property_type' => $this->property_type?->value,
            'region' => $this->region,
            'min_bedrooms' => $this->min_bedrooms,
            'max_price' => $this->max_price,
        ];
    }

    /**
     * @param  Builder<SavedSearch>  $query
     */
    public function scopeMatchingListing(
        Builder $query,
        Listing $listing,
    ): void {
        $listing->loadMissing('branch');

        $query
            ->where(function (Builder $query) use ($listing): void {
                $query
                    ->whereNull('property_type')
                    ->orWhere(
                        'property_type',
                        $listing->property_type->value,
                    );
            })
            ->where(function (Builder $query) use ($listing): void {
                $query
                    ->whereNull('region')
                    ->orWhere(
                        'region',
                        $listing->branch->region,
                    );
            })
            ->where(function (Builder $query) use ($listing): void {
                $query
                    ->whereNull('min_bedrooms')
                    ->orWhere(
                        'min_bedrooms',
                        '<=',
                        $listing->bedrooms,
                    );
            })
            ->where(function (Builder $query) use ($listing): void {
                $query
                    ->whereNull('max_price')
                    ->orWhere(
                        'max_price',
                        '>=',
                        $listing->price,
                    );
            });
    }
}
