<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New website enquiry</title>
</head>
<body style="margin:0;padding:24px;background:#f8fafc;color:#0f172a;font-family:Arial,sans-serif;line-height:1.6;">
    <div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
        <div style="padding:24px;background:#0f172a;color:#ffffff;">
            <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#5eead4;">M2026 website lead</p>
            <h1 style="margin:0;font-size:24px;line-height:1.25;">New enquiry from {{ $lead->name }}</h1>
        </div>

        <div style="padding:24px;">
            <table role="presentation" style="width:100%;border-collapse:collapse;">
                <tr>
                    <th scope="row" style="width:150px;padding:8px 12px 8px 0;text-align:left;vertical-align:top;color:#475569;">Source</th>
                    <td style="padding:8px 0;">{{ $sourceLabel }}</td>
                </tr>
                @if ($lead->subject)
                    <tr>
                        <th scope="row" style="padding:8px 12px 8px 0;text-align:left;vertical-align:top;color:#475569;">Subject</th>
                        <td style="padding:8px 0;">{{ $lead->subject }}</td>
                    </tr>
                @endif
                @if ($lead->email)
                    <tr>
                        <th scope="row" style="padding:8px 12px 8px 0;text-align:left;vertical-align:top;color:#475569;">Email</th>
                        <td style="padding:8px 0;"><a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></td>
                    </tr>
                @endif
                @if ($lead->phone)
                    <tr>
                        <th scope="row" style="padding:8px 12px 8px 0;text-align:left;vertical-align:top;color:#475569;">Phone</th>
                        <td style="padding:8px 0;">{{ $lead->phone }}</td>
                    </tr>
                @endif
                @if ($lead->company)
                    <tr>
                        <th scope="row" style="padding:8px 12px 8px 0;text-align:left;vertical-align:top;color:#475569;">Company</th>
                        <td style="padding:8px 0;">{{ $lead->company }}</td>
                    </tr>
                @endif
                @if ($lead->page_url)
                    <tr>
                        <th scope="row" style="padding:8px 12px 8px 0;text-align:left;vertical-align:top;color:#475569;">Submitted from</th>
                        <td style="padding:8px 0;"><a href="{{ $lead->page_url }}">{{ $lead->page_url }}</a></td>
                    </tr>
                @endif
                <tr>
                    <th scope="row" style="padding:8px 12px 8px 0;text-align:left;vertical-align:top;color:#475569;">Received</th>
                    <td style="padding:8px 0;">{{ $lead->created_at?->format('j M Y, g:i A') }}</td>
                </tr>
            </table>

            <div style="margin-top:20px;padding:18px;background:#f8fafc;border-radius:12px;white-space:pre-wrap;">{{ $lead->message }}</div>

            @if (is_array($lead->metadata) && $lead->metadata !== [])
                <h2 style="margin:24px 0 8px;font-size:18px;">Additional details</h2>
                <table role="presentation" style="width:100%;border-collapse:collapse;">
                    @foreach ($lead->metadata as $key => $value)
                        @if (is_scalar($value))
                            <tr>
                                <th scope="row" style="width:150px;padding:6px 12px 6px 0;text-align:left;vertical-align:top;color:#475569;">{{ str($key)->replace('_', ' ')->title() }}</th>
                                <td style="padding:6px 0;">{{ $value }}</td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            @endif
        </div>
    </div>
</body>
</html>
