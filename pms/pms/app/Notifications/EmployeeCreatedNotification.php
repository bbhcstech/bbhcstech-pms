<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $employee;
    public $password;

    public function __construct($employee, $password)
    {
        $this->employee = $employee;
        $this->password = $password;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Your Employee Account is Created')
            ->greeting('Hello ' . $this->employee->name . ',')
            ->line('Your employee profile has been created successfully.')
            ->line('You can login using the following details:')
            ->line('Email: ' . $this->employee->email)
            ->line('Temporary Password: ' . $this->password)
            ->action('Login Now', url('/login'))
            ->salutation('Regards, Xinksoft HR');
    }
}
