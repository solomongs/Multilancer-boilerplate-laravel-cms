<?php

use App\Modules\Leads\Mail\NewLeadNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('emails configured recipients after a lead is saved', function () {
    Mail::fake();
    config()->set('leads.notification_recipients', [
        'leads@example.com',
        'manager@example.com',
    ]);

    $this->postJson('/contact/submit', [
        'name' => 'Notification Customer',
        'email' => 'customer@example.com',
        'subject' => 'Custom CMS project',
        'message' => 'Please contact me about a new website.',
        'source' => 'quote_form',
    ])->assertCreated();

    Mail::assertSent(NewLeadNotification::class, function (NewLeadNotification $mail): bool {
        return $mail->hasTo('leads@example.com')
            && $mail->hasTo('manager@example.com')
            && $mail->lead->name === 'Notification Customer'
            && $mail->lead->source === 'quote_form';
    });
});

it('does not attempt email delivery when recipients are not configured', function () {
    Mail::fake();
    config()->set('leads.notification_recipients', []);

    $this->postJson('/contact/submit', [
        'name' => 'Stored Only Customer',
        'phone' => '+234 800 000 0000',
        'message' => 'Store this enquiry without sending email.',
    ])->assertCreated();

    Mail::assertNothingSent();
    $this->assertDatabaseHas('module_leads', [
        'name' => 'Stored Only Customer',
        'phone' => '+234 800 000 0000',
    ]);
});
