@php
    $sectionId = is_array($section)
        ? ($section['id'] ?? 'contact')
        : ($section->id ?? 'contact');
    $formId = 'm2026-lead-form-'.$sectionId;
    $source = $content['source'] ?? 'contact_form';
@endphp

<section class="m2026-section m2026-contact-form" data-section-type="contact_form">
    <div class="m2026-container m2026-contact-form__grid">
        <div class="m2026-contact-form__intro">
            @if (! empty($content['eyebrow']))
                <p class="m2026-contact-form__eyebrow">{{ $content['eyebrow'] }}</p>
            @endif

            <h2>{{ $content['heading'] ?? 'Tell us about your project' }}</h2>

            @if (! empty($content['text']))
                <p>{{ $content['text'] }}</p>
            @endif
        </div>

        <div class="m2026-contact-form__panel">
            @if (session('lead_submitted'))
                <div class="m2026-alert m2026-alert--success" role="status">
                    {{ session('lead_submitted') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="m2026-alert m2026-alert--error" role="alert">
                    <p>Please correct the following:</p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="{{ $formId }}" method="POST" action="{{ route('leads.store') }}" class="m2026-form">
                @csrf
                <input type="hidden" name="source" value="{{ $source }}">
                <input type="hidden" name="page_url" value="{{ url()->current() }}">

                <div class="m2026-honeypot" aria-hidden="true">
                    <label for="{{ $formId }}-website">Website</label>
                    <input
                        id="{{ $formId }}-website"
                        type="text"
                        name="website"
                        value=""
                        tabindex="-1"
                        autocomplete="off"
                    >
                </div>

                <div class="m2026-form__grid">
                    <div class="m2026-field">
                        <label for="{{ $formId }}-name">Name</label>
                        <input
                            id="{{ $formId }}-name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            maxlength="120"
                            autocomplete="name"
                            required
                        >
                    </div>

                    <div class="m2026-field">
                        <label for="{{ $formId }}-company">Company</label>
                        <input
                            id="{{ $formId }}-company"
                            type="text"
                            name="company"
                            value="{{ old('company') }}"
                            maxlength="255"
                            autocomplete="organization"
                        >
                    </div>

                    <div class="m2026-field">
                        <label for="{{ $formId }}-email">Email</label>
                        <input
                            id="{{ $formId }}-email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            maxlength="255"
                            autocomplete="email"
                        >
                    </div>

                    <div class="m2026-field">
                        <label for="{{ $formId }}-phone">Phone</label>
                        <input
                            id="{{ $formId }}-phone"
                            type="tel"
                            name="phone"
                            value="{{ old('phone') }}"
                            maxlength="50"
                            autocomplete="tel"
                        >
                    </div>
                </div>

                <p class="m2026-field-help">Provide at least an email address or phone number.</p>

                <div class="m2026-field">
                    <label for="{{ $formId }}-subject">Subject</label>
                    <input
                        id="{{ $formId }}-subject"
                        type="text"
                        name="subject"
                        value="{{ old('subject', $content['subject'] ?? '') }}"
                        maxlength="255"
                    >
                </div>

                <div class="m2026-field">
                    <label for="{{ $formId }}-message">Message</label>
                    <textarea
                        id="{{ $formId }}-message"
                        name="message"
                        rows="7"
                        maxlength="5000"
                        required
                    >{{ old('message') }}</textarea>
                </div>

                <button class="m2026-button m2026-button--primary" type="submit">
                    {{ $content['button_label'] ?? 'Send enquiry' }}
                </button>
            </form>
        </div>
    </div>
</section>
