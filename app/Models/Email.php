<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Email extends Model
{
    use HasFactory, Uuids, SoftDeletes;

    public const FAILED = 'failed';
    public const POSTED = 'posted';
    public const SENT = 'sent';

    protected $fillable = [
        'recipient_email',
        'sender_email',
        'subject',
        'text_content',
        'html_content',
        'attachments',
        'status'
    ];

    protected $hidden = [
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'attachments' => 'array'
    ];

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->save();
    }
}
