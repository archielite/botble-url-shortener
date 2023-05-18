<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::rename('analytics', 'short_url_analytics');
    }

    public function down(): void
    {
        Schema::dropIfExists('short_url_analytics');
    }
};
