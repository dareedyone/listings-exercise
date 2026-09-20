<?php

use App\Models\Listing;
use App\Models\SavedSearch;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_search_matches', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(SavedSearch::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(Listing::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'saved_search_id',
                'listing_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_search_matches');
    }
};
