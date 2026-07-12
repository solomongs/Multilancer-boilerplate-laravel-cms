<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('module_cms_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->string('template')->default('default');
            $table->string('status')->default('draft')->index();
            $table->boolean('is_homepage')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots')->default('index,follow');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('module_cms_page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('module_cms_pages')->cascadeOnDelete();
            $table->string('section_type');
            $table->string('section_name')->nullable();
            $table->json('content')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamps();

            $table->index(['page_id', 'is_enabled', 'sort_order'], 'cms_page_sections_render_index');
        });

        Schema::create('module_cms_page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('module_cms_pages')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('snapshot');
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();

            $table->index(['page_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_cms_page_revisions');
        Schema::dropIfExists('module_cms_page_sections');
        Schema::dropIfExists('module_cms_pages');
    }
};
