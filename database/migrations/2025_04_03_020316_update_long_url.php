<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('short_urls', function (Blueprint $table) {
            $table->text('long_url')->change();
            $table->string('short_url', 150)->change();
        });
    }

    public function down(): void
    {
        Schema::table('short_urls', function (Blueprint $table) {
            $table->string('long_url', 255)->change();
            $table->string('short_url', 30)->change();
        });
    }
};
