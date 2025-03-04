<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage as SlackMessage;

class UserWelcomeNotification extends Notification
{
    use Queueable;

    private User $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail','slack'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
        ->subject("Let’s Shape the Future Together!")
        ->markdown('mail.user.welcome');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param object $notifiable
     * @return SlackMessage
     */
    public function toSlack(object $notifiable): SlackMessage
    {
        return (new SlackMessage)
            ->headerBlock('New Account')
            ->sectionBlock(function (SectionBlock $block) {
                $block->text('User ID. #'.$this->user->id);
                $block->field("*Name:*\n".$this->user->firstname.' '.$this->user->name)->markdown();
                $block->field("*Email:*\n".$this->user->email)->markdown();
            });
    }
}
