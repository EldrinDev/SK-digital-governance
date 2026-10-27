<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonalDataController extends Controller
{
    /**
     * Download everything the platform keeps about the signed-in youth,
     * as the Data Privacy Act's right of access provides.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->load([
            'barangay:id,name,logo_path',
            'eventRegistrations.event:id,title,starts_at,venue',
            'feedback.event:id,title',
            'surveyResponses.survey:id,title',
            'surveyResponses.answers.question:id,prompt',
        ]);

        AuditLog::record('account.data_exported', $user);

        $data = [
            'exported_at' => now()->toIso8601String(),
            'account' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->label(),
                'barangay' => $user->barangay?->name,
                'created_at' => $user->created_at?->toIso8601String(),
                'privacy_consented_at' => $user->privacy_consented_at?->toIso8601String(),
            ],
            'event_registrations' => $user->eventRegistrations->map(fn ($registration): array => [
                'event' => $registration->event->title,
                'starts_at' => $registration->event->starts_at->toIso8601String(),
                'venue' => $registration->event->venue,
                'registered_at' => $registration->created_at->toIso8601String(),
                'attended_at' => $registration->attended_at?->toIso8601String(),
                'checked_in_via' => $registration->check_in_method?->label(),
            ])->all(),
            'feedback' => $user->feedback->map(fn ($feedback): array => [
                'event' => $feedback->event?->title,
                'rating' => $feedback->rating,
                'message' => $feedback->message,
                'sent_at' => $feedback->created_at->toIso8601String(),
                'reply' => $feedback->response,
            ])->all(),
            'survey_responses' => $user->surveyResponses->map(fn ($response): array => [
                'survey' => $response->survey->title,
                'answered_at' => $response->created_at->toIso8601String(),
                'answers' => $response->answers->map(fn ($answer): array => [
                    'question' => $answer->question->prompt,
                    'answer' => $answer->value,
                ])->all(),
            ])->all(),
        ];

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="my-sk-data.json"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
