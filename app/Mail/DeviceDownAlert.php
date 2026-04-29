<?php

namespace App\Mail;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DeviceDownAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $alert;

    public function __construct(Alert $alert)
    {
        $this->alert = $alert;
    }

    public function build()
    {
        return $this->subject($this->alert->title)
                    ->markdown('emails.device-down-alert', [
                        'alert' => $this->alert
                    ]);
    }
}
