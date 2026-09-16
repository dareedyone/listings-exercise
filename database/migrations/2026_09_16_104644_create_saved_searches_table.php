<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();

            $table->unsignedInteger('min_price')->nullable();
            $table->unsignedInteger('max_price')->nullable();

            $table->unsignedTinyInteger('min_bedrooms')->nullable();
            $table->unsignedTinyInteger('max_bedrooms')->nullable();

            $table->unsignedTinyInteger('min_bathrooms')->nullable();
            $table->unsignedTinyInteger('max_bathrooms')->nullable();

            $table->string('property_type')->nullable();
            $table->string('status')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
