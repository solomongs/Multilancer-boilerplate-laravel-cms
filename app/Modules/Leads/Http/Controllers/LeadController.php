<?php

namespace App\Modules\Leads\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Leads\Http\Requests\StoreLeadRequest;
use App\Modules\Leads\Mail\NewLeadNotification;
use App\Modules\Leads\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LeadController extends Controller
{
    public function store(StoreLeadRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        unset($data['website']);

        $ipAddress = $request->ip();
        $applicationKey = (string) config('app.key', '');
        $userAgent = $request->userAgent();
        $metadata = is_array($data['metadata'] ?? null) ? $data['metadata'] : [];

        if ($request->headers->has('referer')) {
            $metadata['referrer'] = $request->headers->get('referer');
        }

        $lead = Lead::query()->create([
            ...$data,
            'status' => config('leads.default_status', 'new'),
            'ip_hash' => is_string($ipAddress) && $ipAddress !== ''
                ? hash_hmac('sha256', $ipAddress, $applicationKey)
                : null,
            'user_agent' => is_string($userAgent) && $userAgent !== ''
                ? substr($userAgent, 0, 1024)
                : null,
            'metadata' => $metadata === [] ? null : $metadata,
        ]);

        $this->sendNotification($lead);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Thank you. Your enquiry has been received.',
                'lead_id' => $lead->getKey(),
            ], 201);
        }

        return redirect()->back()->with(
            'lead_submitted',
            'Thank you. Your enquiry has been received.',
        );
    }

    private function sendNotification(Lead $lead): void
    {
        $recipients = config('leads.notification_recipients', []);

        if (! is_array($recipients) || $recipients === []) {
            return;
        }

        try {
            Mail::to($recipients)->send(new NewLeadNotification($lead));
        } catch (\Throwable $exception) {
            Log::warning('Lead saved but notification email could not be sent.', [
                'lead_id' => $lead->getKey(),
                'exception' => $exception,
            ]);
        }
    }
}
