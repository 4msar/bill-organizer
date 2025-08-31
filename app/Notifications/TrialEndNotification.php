<?php

namespace App\Notifications;

use App\Models\Bill;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class TrialEndNotification extends Notification
{
    use Queueable;

    protected $bill;

    /**
     * Create a new notification instance.
     */
    public function __construct(Bill $bill)
    {
        $this->bill = $bill;
        $this->bill->load('team'); // Ensure the team is loaded for currency symbol
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
        $this->bill->load('team');
        $currency = $this->bill->team->currency_symbol ?? '$';
        $amount = $this->bill->amount;

        return (new MailMessage())
            ->subject('Trial Period Ending Soon')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your trial period for {$this->bill->title} ends on {$this->bill->trial_end_date->format('M d, Y')}.")
            ->line("After the trial ends, your first payment of {$currency}{$amount} will be due on {$this->bill->due_date->format('M d, Y')}.")
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
            'trial_end_date' => $this->bill->trial_end_date,
            'due_date' => $this->bill->due_date,
            'amount' => $this->bill->amount,
        ];
    }

    public static function getDescription($notification): string
    {
        $trialEndDate = Carbon::parse($notification->data['trial_end_date'])->format('d M, Y');
        $dueDate = Carbon::parse($notification->data['due_date'])->format('d M, Y');

        return "Trial period for \"{$notification->data['title']}\" ends on {$trialEndDate}. First payment of " . self::getAmount($notification) . " due on {$dueDate}.";
    }

    public static function getAmount($notification): string
    {
        $currency = Team::current()->currency_symbol ?? "$";
        $amount = $notification->data['amount'];

        return "{$currency}{$amount}";
    }
}
