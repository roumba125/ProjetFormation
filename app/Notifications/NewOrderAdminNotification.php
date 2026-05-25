<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderAdminNotification extends Notification implements ShouldQueue
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
            ->subject("🔔 Nouvelle commande {$order->order_number}")
            ->greeting("Nouvelle commande reçue !")
            ->line("**Client :** {$order->client_name}")
            ->line("**Téléphone :** {$order->client_phone}")
            ->line("**Total : " . number_format($order->total_amount, 0, ',', ' ') . " FCFA**")
            ->line("**Livraison :** {$order->delivery_method_label}")
            ->action('Gérer dans l\'admin', url("/admin/orders/{$order->id}"))
            ->salutation("BergerPro — Système");
    }
}
