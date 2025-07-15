<?php

namespace App\Notifications;

use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class TrialExpiredNotification extends Notification
{
    use Queueable;

    protected $bill;

    /**
     * Create a new notification instance.
     */
    public function __construct(Bill $bill)
    {
        $this->bill = $bill;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return $notifiable->getNotificationChannels() ?? [];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('Trial Expired: ' . $this->bill->title)
            ->greeting("Hello {$notifiable->name},")
            ->line("Your trial period for '{$this->bill->title}' has expired.")
            ->line("Trial ended on: {$this->bill->trial_end_date->format('M d, Y')}")
            ->line("Amount: {$this->bill->amount}")
            ->action('Convert to Regular Bill', route('bills.show', $this->bill->id))
            ->line('Convert now to activate your service and avoid any interruptions!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'bill_id' => $this->bill->id,
            'title' => $this->bill->title,
            'trial_end_date' => $this->bill->trial_end_date,
            'amount' => $this->bill->amount,
            'type' => 'trial_expired',
        ];
    }

    public static function getDescription($notification): string
    {
        $date = Carbon::parse($notification->data['trial_end_date'])->format('d M, Y');
        
        return "Trial for \"{$notification->data['title']}\" expired on {$date}. Please convert to continue service.";
    }
}