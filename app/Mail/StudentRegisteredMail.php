<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;

    /**
     * Create a new message instance.
     */
    public function __construct($student)
    {
        $this->student = $student;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Registration Confirmation')
                    ->view('emails.student_registered')
                    ->with([
                        'name' => $this->student->s_candidate_name,
                        'email' => $this->student->s_email,
                        'form_number' => $this->student->s_appl_form_num
                    ]);
    }
}
