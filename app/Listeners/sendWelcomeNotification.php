<?php

namespace App\Listeners;

use App\Events\Welcome;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\User\Entities\User;
use Mail;

class sendWelcomeNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Welcome  $event
     * @return void
     */
    public function handle(Welcome $event)
    {
        $user = User::find($event->userId)->toArray();
        
        //Welcome Message to User
        Mail::send('emails.welcome', ["user"=>$user], function($message) use ($user) {
            $message->to($user['email']);
            $message->subject('Welcome on Alysei');
        });

        //New Candidate Message to Admin
        $user['to'] = env("mail_email");

        Mail::send('emails.new_user', ["user"=>$user], function($message) use ($user) {
            $message->to($user['to']);
            $message->subject('New User Registration Mail');
        });
    }
}
