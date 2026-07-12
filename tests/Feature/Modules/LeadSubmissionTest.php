<?php

use App\Modules\Leads\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores a valid website enquiry and redirects HTML submissions back', function () {
    $this->from('/contact')
        ->withHeader('Referer', 'https://multilancer.test/contact')
        ->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => 'M2026 Contact Form Test Browser',
        ])
        ->post('/contact/submit', [
            'name' => 'Ada Customer',
            'email' => 'ada@example.com',
            'subject' => 'Website development',
            'message' => 'I need a company website and CMS.',
        ])
        ->assertRedirect('/contact')
        ->assertSessionHas('lead_submitted');

    $lead = Lead::query()->firstOrFail();

    expect($lead->name)->toBe('Ada Customer')
        ->and($lead->email)->toBe('ada@example.com')
        ->and($lead->source)->toBe('contact_form')
        ->and($lead->status)->toBe('new')
        ->and($lead->ip_hash)->toHaveLength(64)
        ->and($lead->ip_hash)->not->toBe('203.0.113.10')
        ->and($lead->user_agent)->toBe('M2026 Contact Form Test Browser')
        ->and($lead->metadata['referrer'])->toBe('https://multilancer.test/contact');
});

it('accepts JSON enquiries when at least one contact method is supplied', function () {
    $this->postJson('/contact/submit', [
        'name' => 'Phone Customer',
        'phone' => '+234 801 234 5678',
        'message' => 'Please call me about a training programme.',
        'source' => 'course_enquiry',
    ])
        ->assertCreated()
        ->assertJsonPath('message', 'Thank you. Your enquiry has been received.')
        ->assertJsonStructure(['lead_id']);

    $this->assertDatabaseHas('module_leads', [
        'name' => 'Phone Customer',
        'phone' => '+234 801 234 5678',
        'source' => 'course_enquiry',
        'status' => 'new',
    ]);
});

it('rejects submissions without contact details and bot honeypot submissions', function () {
    $this->postJson('/contact/submit', [
        'name' => 'No Contact',
        'message' => 'This submission has no email or phone.',
    ])->assertUnprocessable()->assertJsonValidationErrors(['email', 'phone']);

    $this->postJson('/contact/submit', [
        'name' => 'Bot Submission',
        'email' => 'bot@example.com',
        'message' => 'Automated spam.',
        'website' => 'https://spam.example',
    ])->assertUnprocessable()->assertJsonValidationErrors(['website']);

    expect(Lead::query()->count())->toBe(0);
});

it('rate limits repeated public lead submissions', function () {
    $payload = [
        'name' => 'Rate Limit Test',
        'email' => 'rate@example.com',
        'message' => 'Testing submission limits.',
    ];

    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.25'])
            ->postJson('/contact/submit', $payload)
            ->assertCreated();
    }

    $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.25'])
        ->postJson('/contact/submit', $payload)
        ->assertTooManyRequests();
});

it('records the first meaningful contact timestamp when a lead advances', function () {
    $lead = Lead::query()->create([
        'name' => 'Workflow Lead',
        'email' => 'workflow@example.com',
        'message' => 'Please contact me.',
        'source' => 'contact_form',
        'status' => 'new',
    ]);

    expect($lead->contacted_at)->toBeNull();

    $lead->update(['status' => 'contacted']);

    expect($lead->fresh()->contacted_at)->not->toBeNull();
});
