<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('module_cms_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('source_path')->unique();
            $table->string('destination_url');
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('is_enabled')->default(true)->index();
            $table->unsignedBigInteger('hit_count')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->timestamps();

            $table->index(['is_enabled', 'source_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_cms_redirects');
    }
};
