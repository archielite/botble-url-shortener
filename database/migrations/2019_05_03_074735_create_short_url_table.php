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
    }

    public function down(): void
    {
        Schema::dropIfExists('short_urls');
    }
};
