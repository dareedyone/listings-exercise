<?php

namespace App\Http\Controllers;

use App\Http\Resources\AlertResource;
use App\Models\SavedSearchMatch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AlertController extends Controller
{
    public function index(Request $request): Response
    {
        $matches = SavedSearchMatch::query()
            ->whereIn(
                'saved_search_id',
                $request->user()->savedSearches()->select('id'),
            )
            ->with([
                'listing.branch',
                'savedSearch',
            ])
            ->latest()
            ->get();

        $alerts = $matches
            ->groupBy('listing_id')
            ->map(function ($matches) {
                return [
                    'listing' => $matches->first()->listing,
                    'saved_searches' => $matches
                        ->map(fn ($match) => $match->savedSearch)
                        ->values(),
                ];
            })
            ->values();

        return Inertia::render('Alerts/Index', [
            'alerts' => AlertResource::collection($alerts),
        ]);
    }
}
