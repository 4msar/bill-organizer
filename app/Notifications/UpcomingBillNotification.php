<?php

namespace App\Notifications;

use App\Models\Bill;
use App\Models\Team;
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
            ->subject('Upcoming Bill Reminder : ' . $this->bill->title)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Just a quick heads-up! 🌼')
            ->line("Your bill for **{$this->bill->title}** is coming up, and it’s due on **{$this->bill->due_date->format('M d, Y')}**.")
            ->line('Here’s a quick summary:')
            ->line('-----------------------------------------')
            ->line("Amount: {$currency}{$amount}")
            ->line('Status: Pending')
            ->line('-----------------------------------------')
            ->action('View Bill', route('bills.show', $this->bill->id))
            ->line('If you have any questions or need help with anything, feel free to reach out — we’re always happy to assist.')
            ->line('Thanks for being with us! 💛')
            ->salutation(str("Warm regards,<br/>Bill Organizer Team")->toHtmlString());
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

        return "Your \"{$notification->data['title']}\" bill is due on {$date}, amount of " . self::getAmount($notification) . '.';
    }

    public static function getAmount($notification): string
    {
        $currency = Team::current()->currency_symbol ?? '$';
        $amount = $notification->data['amount'];

        return "{$currency}{$amount}";
    }
}
