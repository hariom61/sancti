<?php

namespace Sancti\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class PasswordMail extends Mailable
{
	use Queueable, SerializesModels;

	public $user;
	public $password;

	public function __construct(User $user, $password)
	{
		$this->user = $user;
		$this->password = $password;
	}

	public function build()
	{
		return $this->view('sancti::emails.password');
	}
}
