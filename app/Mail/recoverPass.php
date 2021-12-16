<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class recoverPass extends Mailable
{
    use Queueable, SerializesModels;

    public $pass;
    /*public $asunto
    public $datos
    public $titulo*/
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($pass)
    {
        $this->pass = $pass;
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Solicitud de nueva contraseña')-> view('recoverPassword');
        //si esta en una carpeta hay que poner un .
    }
}
