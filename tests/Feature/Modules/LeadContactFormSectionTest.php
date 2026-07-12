<?php

use App\Modules\Cms\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders a configured contact form section on a published CMS page', function () {
    $this->withoutVite();

    $page = Page::query()->create([
        'title' => 'Contact Us',
        'slug' => 'contact',
        'template' => 'contact',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $page->sections()->create([
        'section_type' => 'contact_form',
        'section_name' => 'Main enquiry form',
        'content' => [
            'eyebrow' => 'Start a project',
            'heading' => 'Build with Multilancer',
            'text' => 'Tell us what you need and our team will respond.',
            'source' => 'quote_form',
            'button_label' => 'Request a quote',
        ],
        'sort_order' => 10,
        'is_enabled' => true,
    ]);

    $this->get('/contact')
        ->assertOk()
        ->assertSee('Build with Multilancer')
        ->assertSee('Request a quote')
        ->assertSee('action="'.route('leads.store').'"', false)
        ->assertSee('name="source" value="quote_form"', false)
        ->assertSee('name="website"', false)
        ->assertSee('name="email"', false)
        ->assertSee('name="phone"', false)
        ->assertSee('name="message"', false);
});
