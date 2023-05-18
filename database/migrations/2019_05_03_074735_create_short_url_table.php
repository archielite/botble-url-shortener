<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('short_urls', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('long_url', 255);
            $table->string('short_url', 30);
            $table->integer('user_id')->nullable();
            $table->string('status', 60)->default('published');
        });

        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->string('short_url', 30);
            $table->tinyInteger('click')->nullable()->default(0);
            $table->tinyInteger('real_click')->nullable()->default(0);
            $table->char('country', 10)->nullable()->default(0);
            $table->string('country_full', 50)->nullable()->default(0);
            $table->string('referer', 300)->nullable();
            $table->string('ip_address', 39)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('short_urls');
        Schema::dropIfExists('analytics');
    }
};
