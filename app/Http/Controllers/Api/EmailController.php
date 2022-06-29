<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Minisend\Api\Email;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function getEmails(Request $request) : JsonResponse
    {
        try {
            return response()->json(Email::getAllEmails());
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
    }

    public function getEmail(Request $request, string $emailId): JsonResponse
    {
        try {
            $email = Email::getEmail($emailId);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'The requested email could not be found',
            ])->setStatusCode(Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'email' => $email
        ]);
    }

    public function sendEmail(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'recipient_email' => 'required|string|email',
                'sender_email' => 'required|string|email',
                'subject' => 'required|string',
                'text_content' => 'required_without:html_content|string',
                'html_content' => 'required_without:text_content|string',

                'attachments' => 'sometimes',
                'attachments.*' => 'mimes:doc,pdf,docx,zip'
            ]);

            $uploaded_attachments = [];
            $emailParameters = $request->except([
                'attachments'
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $attachment) {
                    $uploaded_attachments[] = Email::uploadAttachments($attachment);
                }

                $emailParameters = array_merge($emailParameters, [
                    'uploaded_attachments' => $uploaded_attachments
                ]);
            }

            Email::createNewEmailRequest($emailParameters);

            return response()->json('success');

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
    }

    public function getEmailStats(Request $request): JsonResponse
    {
        try {
            $stats = Email::getEmailStats();
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'stats' => $stats
        ]);
    }

    public function searchEmails(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'sender_email' => 'sometimes|string|email',
                'recipient_email' => 'sometimes|string|email',
                'subject' => 'sometimes|string'
            ]);

            $emails = Email::searchEmails($request->all());
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return response()->json($emails);
    }
}
