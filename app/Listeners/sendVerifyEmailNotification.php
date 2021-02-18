<?php

namespace App\Listeners;

use App\Events\sendVerifyEmailNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\User\Entities\User;
use Mail;

class sendVerifyEmailNotification
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
    public function handle(sendVerifyEmailNotification $event)
    {
        $user = User::find($event->userId)->toArray();
        
        
        Mail::send('emails.verify_email', ["user"=>$user], function($message) use ($user) {
            $message->from('no-reply@alysei.com');
            $message->to($user['email']);
            $message->subject('Verify Your Email');
        });

        
    }
}
