<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->string('cover_image_alt')->nullable()->after('cover_image');
            $table->string('cover_image_caption')->nullable()->after('cover_image_alt');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn(['cover_image_alt', 'cover_image_caption']);
        });
    }
};
