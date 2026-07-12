<?php

return [
    'default_status' => 'new',
    'notification_recipients' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('LEADS_NOTIFICATION_EMAILS', '')),
    ), static fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)),
    'statuses' => [
        'new' => 'New',
        'contacted' => 'Contacted',
        'qualified' => 'Qualified',
        'proposal' => 'Proposal Sent',
        'won' => 'Won',
        'lost' => 'Lost',
        'spam' => 'Spam',
    ],
    'sources' => [
        'contact_form' => 'Contact Form',
        'quote_form' => 'Quote Form',
        'course_enquiry' => 'Course Enquiry',
        'hosting_enquiry' => 'Hosting Enquiry',
        'landing_page' => 'Landing Page',
        'manual' => 'Manual',
    ],
];
