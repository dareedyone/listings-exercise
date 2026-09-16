<?php

namespace App\Http\Controllers;

use App\Enums\PropertyType;
use App\Http\Requests\StoreSavedSearchRequest;
use App\Http\Resources\BranchResource;
use App\Http\Resources\SavedSearchResource;
use App\Models\Branch;
use App\Models\SavedSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SavedSearchController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('SavedSearches/Create', [
            'filters' => $request->only([
                'property_type',
                'region',
                'min_bedrooms',
                'max_price',
            ]),
            'branches' => BranchResource::collection(
                Branch::query()->orderBy('name')->get(),
            ),
            'propertyTypes' => PropertyType::options(),
        ]);
    }

    public function store(StoreSavedSearchRequest $request): RedirectResponse
    {
        SavedSearch::create([
            'user_id' => $request->user()->id,
            ...$request->validated(),
        ]);

        return redirect()->route('saved-searches.index');
    }

    public function index(Request $request): Response
    {
        return Inertia::render('SavedSearches/Index', [
            'savedSearches' => SavedSearchResource::collection(
                $request->user()
                    ->savedSearches()
                    ->latest()
                    ->get()
            ),
        ]);
    }

    public function destroy(
        Request $request,
        SavedSearch $savedSearch,
    ): RedirectResponse {
        abort_unless(
            $savedSearch->user_id === $request->user()->id,
            403,
        );

        $savedSearch->delete();

        return redirect()->route('saved-searches.index');
    }
}
