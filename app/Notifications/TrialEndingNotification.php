<?php

namespace App\Notifications;

use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class TrialEndingNotification extends Notification
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
        $daysLeft = now()->diffInDays($this->bill->trial_end_date);
        
        return (new MailMessage())
            ->subject('Trial Ending Soon: ' . $this->bill->title)
            ->greeting("Hello {$notifiable->name},")
            ->line("Your trial period for '{$this->bill->title}' is ending in {$daysLeft} day(s).")
            ->line("Trial ends on: {$this->bill->trial_end_date->format('M d, Y')}")
            ->line("Amount: {$this->bill->amount}")
            ->action('Convert to Regular Bill', route('bills.show', $this->bill->id))
            ->line('Convert now to continue using this service!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        $daysLeft = now()->diffInDays($this->bill->trial_end_date);
        
        return [
            'bill_id' => $this->bill->id,
            'title' => $this->bill->title,
            'trial_end_date' => $this->bill->trial_end_date,
            'amount' => $this->bill->amount,
            'days_left' => $daysLeft,
            'type' => 'trial_ending',
        ];
    }

    public static function getDescription($notification): string
    {
        $date = Carbon::parse($notification->data['trial_end_date'])->format('d M, Y');
        $daysLeft = $notification->data['days_left'] ?? 0;
        
        return "Trial for \"{$notification->data['title']}\" ends in {$daysLeft} day(s) on {$date}. Convert to continue service.";
    }
}