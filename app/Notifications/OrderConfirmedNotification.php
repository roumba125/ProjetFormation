<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $order = $this->order->load('items');

        return (new MailMessage)
            ->subject("✅ Commande {$order->order_number} reçue — BergerPro")
            ->greeting("Bonjour {$order->client_name} !")
            ->line("Votre commande **{$order->order_number}** a bien été reçue.")
            ->line("**Total : " . number_format($order->total_amount, 0, ',', ' ') . " FCFA**")
            ->line("**Mode de réception :** {$order->delivery_method_label}")
            ->line("Nous vous contacterons sous **24h** au **{$order->client_phone}**.")
            ->action('Suivre ma commande', url("/suivi?ref={$order->order_number}&email={$order->client_email}"))
            ->line("Merci pour votre confiance ! 🐑")
            ->salutation("L'équipe BergerPro");
    }
}
