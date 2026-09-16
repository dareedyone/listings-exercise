<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @mixin Collection
 */
class AlertResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'listing' => new ListingResource($this['listing']),
            'saved_searches' => SavedSearchResource::collection(
                $this['saved_searches'],
            ),
        ];
    }
}
