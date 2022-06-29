<?php

namespace App\Jobs;

use App\Mail\SendEmail as SendEmailMessage;
use App\Models\Email as Model;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $maxExceptions = 2;

    private Model $emailModel;
    private array $emailPayload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $emailModel, array $emailPayload)
    {
        $this->emailModel = $emailModel;
        $this->emailPayload = $emailPayload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->emailPayload['recipient_email'])->send(new SendEmailMessage($this->emailPayload));
        $this->emailModel->setStatus(Model::SENT);
    }

    /**
     * Executes when the job fails.
     *
     * @return void
     */
    public function failed()
    {
        $this->emailModel->setStatus(Model::FAILED);
    }
}
