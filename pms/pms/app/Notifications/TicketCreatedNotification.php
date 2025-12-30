<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketCreatedNotification extends Notification
{
    use Queueable;

    public $ticket;

    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    // send both to database and by email
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    // database payload
    public function toDatabase($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => 'New Ticket Assigned',
            'message' => 'A new ticket has been assigned to you: ' . $this->ticket->subject,
            'url' => url('/admin/tickets/' . $this->ticket->id),
            'created_by' => auth()->id(),
        ];
    }

    // email message
public function toMail($notifiable)
{
    $ticket = $this->ticket;
    $ticketUrl = url('/admin/tickets/' . $ticket->id);

    return (new MailMessage)
        ->markdown('emails.default') // <--- your custom layout here
        ->subject('New Ticket Assigned: ' . $ticket->subject)
        ->greeting('Hello ' . ($notifiable->name ?? 'User') . ',')
        ->line('A new ticket has been assigned to you.')
        ->line('Subject: ' . $ticket->subject)
        ->line('Priority: ' . ($ticket->priority ?? 'N/A'))
        ->action('View Ticket', $ticketUrl)
        ->line('You can also view this ticket in the PMS dashboard.');
}

}
