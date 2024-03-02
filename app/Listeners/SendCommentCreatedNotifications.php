<?php
declare(type_strict=1);
namespace App\Listeners;

use App\Models\User;
use App\Events\CommentCreatedEvent;
use App\Notifications\NewCommentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCommentCreatedNotifications implements ShouldQueue
{
    public function handle(CommentCreatedEvent $event): void
    {
        foreach(User::whereNot('id', $event->comment->user_id)->cursor() as $user){
            $user->notify(New NewCommentNotification($event->comment));
        }
    }
}
