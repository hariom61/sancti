<?php

namespace Sancti\Http\Traits;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait AuthHelper
{
	function verifyEmail(User $user)
	{
		$this->checkUser($user);

		if(empty($user->email_verified_at)) {
			throw new Exception("Account not activated.");
		}

		return $user;
	}

	function activateEmail(?User $user)
	{
		$this->checkUser($user);

		$user->email_verified_at = now();
		$user->save();

		return $user;
	}

	function createCode(User $user)
	{
		$this->checkUser($user);

		$user->code = uniqid();
		$user->ip = request()->ip();
		$user->save();

		return $user;
	}

	function updatePassword(User $user, $password)
	{
		$this->checkUser($user);

		$user->password = Hash::make($password);
		$user->ip = request()->ip();
		$user->save();

		return $user;
	}

	function checkUser(?User $user)
	{
		if(empty($user) || empty($user->id) || empty($user->email)) {
			throw new Exception("User not found.");
		}

		return $user;
	}

	function cleanName($name)
	{
		return htmlentities(strip_tags($name), ENT_QUOTES, 'utf-8');
	}

	function jsonPretty($user)
	{
		return $user->toJson(JSON_PRETTY_PRINT);
	}
}