<?php

namespace App\Notifications;

use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class UpcomingBillNotification extends Notification
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
        $channels = [];

        if ($notifiable?->getMeta('email_notification', false)) {
            $channels[] = 'mail';
        }

        if ($notifiable?->getMeta('web_notification', false)) {
            $channels[] = 'database';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('Reminder: Upcoming Bill Due')
            ->greeting("Hello {$notifiable->name},")
            ->line("This is a reminder that your bill, {$this->bill->title}, is due on {$this->bill->due_date->format('M d, Y')}.")
            ->line("Amount: {$this->bill->amount}")
            ->action('View Bill', route('bills.show', $this->bill->id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'bill_id' => $this->bill->id,
            'title' => $this->bill->title,
            'due_date' => $this->bill->due_date,
            'amount' => $this->bill->amount,
        ];
    }

    public static function getDescription($notification): string
    {
        $date = Carbon::parse($notification->data['due_date'])->format('d M, Y');

        return "Your \"{$notification->data['title']}\" bill is due on {$date}, amount of {$notification->data['amount']}.";
    }
}
