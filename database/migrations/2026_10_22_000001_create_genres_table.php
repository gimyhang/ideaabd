<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Book\Models\Genre;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('genres')) {
            Schema::create('genres', function (Blueprint $table) {
                $table->id();
                $table->string('slug', 100)->unique();           // permanent key stored in books.genre_category
                $table->string('name_en', 150);
                $table->string('name_bn', 150)->nullable();
                $table->string('icon', 60)->nullable();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        // The ten genres that were hard-coded in the admin and publisher forms. Existing books keep
        // their slug, so no book data needs to change.
        $order = 0;
        foreach (Genre::DEFAULTS as $slug => [$en, $bn]) {
            DB::table('genres')->insertOrIgnore([
                'slug' => $slug, 'name_en' => $en, 'name_bn' => $bn, 'sort_order' => ++$order * 10,
                'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('genres');
    }
};
