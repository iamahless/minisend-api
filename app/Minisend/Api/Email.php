<?php

namespace App\Minisend\Api;

use App\Jobs\SendEmail;
use App\Models\Email as Model;
use Illuminate\Support\Facades\Storage;

class Email
{
    private Model $model;

    public function __construct(Model $email)
    {
        $this->model = $email;
    }

    public static function createNewEmailRequest(array $payload): static
    {
        $data = [
            'recipient_email' => $payload['recipient_email'],
            'sender_email' => $payload['sender_email'],
            'subject' => $payload['subject'],
            'text_content' => $payload['text_content'] ?? null,
            'html_content' => $payload['html_content'] ?? null,
            'attachments' => $payload['uploaded_attachments'],
            'status' => Model::POSTED
        ];

        $email = Model::create($data);

        SendEmail::dispatch($email, $payload);

        return new static($email);
    }

    public static function uploadAttachments($attachment): array
    {
        $fileSystem = config('filesystems.default');
        $name = $attachment->getClientOriginalName();
        $type = $attachment->getMimeType();
        $path = Storage::disk($fileSystem)->putFile('public/emails/attachments/'.time(), $attachment, ['visibility' => 'public']);

        return [
            'name' => $name,
            'type' => $type,
            'link' => config('app.url') . Storage::disk($fileSystem)->url($path)
        ];
    }

    public static function getAllEmails()
    {
        return Model::paginate();
    }

    public static function getEmail(string $id)
    {
        return Model::findOrFail($id);
    }

    public static function searchEmails(array $payload)
    {
        return Model::where($payload)->paginate();
    }

    public static function getEmailStats() :array
    {
        return [
            [
                'name' => 'Total Email Posted',
                'stat' => Model::where('status', Model::POSTED)->count()
            ],[
                'name' => 'Total Email Sent',
                'stat' => Model::where('status', Model::SENT)->count()
            ],[
                'name' => 'Total Email Failed',
                'stat' => Model::where('status', Model::FAILED)->count()
            ]
        ];
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getId(): string
    {
        return $this->model->id;
    }

}
