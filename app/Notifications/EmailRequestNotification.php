<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\EmailRequest;
use App\Filament\Resources\EmailRequestResource;

class EmailRequestNotification extends Notification
{
    use Queueable;

    protected $emailRequestId;
    protected $status;
    protected $emailTo;
    protected $companyName;
    protected $subject;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $emailRequestId, string $status, string $emailTo, string $companyName, string $subject)
    {
        $this->emailRequestId = $emailRequestId;
        $this->status = $status;
        $this->emailTo = $emailTo;
        $this->companyName = $companyName;
        $this->subject = $subject;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->emailRequestId,
            'type' => 'email_request',
            'status' => $this->status,
            'email_to' => $this->emailTo,
            'company_name' => $this->companyName,
            'subject' => $this->subject,
            'message' => "Solicitud de email para {$this->companyName}",
            'action_label' => 'Ver solicitud',
            'action_url' => EmailRequestResource::getUrl('edit', ['record' => $this->emailRequestId]),
        ];
    }
}
