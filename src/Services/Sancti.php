<?php

namespace Sancti\Services;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Sancti\Mail\PasswordMail;
use Sancti\Mail\RegisterMail;
use Sancti\Http\Traits\AuthHelper;
use Sancti\Http\Requests\LoginRequest;
use Sancti\Http\Requests\ActivateRequest;
use Sancti\Http\Requests\RegisterRequest;
use Sancti\Http\Requests\ResetPasswordRequest;
use Sancti\Http\Requests\ChangePasswordRequest;

class Sancti
{
	use AuthHelper;

	function login(LoginRequest $request)
	{
		if (Auth::once($request->validated())) {
			try {
				$this->verifyEmail($request->user());
			} catch (Exception $e) {
				Log::error($e->getMessage());
				throw new Exception("Confirm email address.", 402);
			}
			return ['token' => $request->user()->createToken('sancti_app')->plainTextToken];
		} else {
			throw new Exception("Invalid credentials.", 402);
		}
	}

	function register(RegisterRequest $request)
	{
		$user = null;
		$valid = $request->validated();

		try {
			$user = User::create([
				'name' => $this->cleanName($valid['name']),
				'email' => $valid['email'],
				'password' => Hash::make($valid['password'])
			]);
			$user = $this->createCode($user);
		} catch (Exception $e) {
			Log::error($e->getMessage());
			throw new Exception("Can not create user.", 402);
		}

		try {
			Mail::to($user->email)->send(new RegisterMail($user));
		} catch (Exception $e) {
			$user->delete();
			Log::error($e->getMessage());
			throw new Exception("Unable to send e-mail, please try again later.", 402);
		}

		return ['message' => 'Account has been created, please confirm your email address.'];
	}

	function activate(ActivateRequest $request)
	{
		$valid = $request->validated();

		try {
			$user = User::where('id', $valid['id'])->whereNotNull('code')->where('code', $valid['code'])->first();
			$this->activateEmail($user);
		} catch (Exception $e) {
			Log::error($e->getMessage());
			throw new Exception("Invalid activation code.", 402);
		}

		return ['message' => 'Email has been confirmed.'];
	}

	function logout(Request $request)
	{
		$request->user()->tokens()->delete();
		return ['message' => 'Logged out.'];
	}

	function reset(ResetPasswordRequest $request)
	{
		$valid = $request->validated();

		try {
			$user = User::where(['email' => $valid['email']])->first();
		} catch (Exception $e) {
			Log::error($e->getMessage());
			throw new Exception("Database error.", 402);
		}

		$password = uniqid();
		$user = $this->updatePassword($user, $password);
		$user = $this->activateEmail($user);

		try {
			Mail::to($user)->send(new PasswordMail($user, $password));
		} catch (Exception $e) {
			Log::error($e->getMessage());
			throw new Exception("Unable to send e-mail, please try again later.");
		}

		return ['message' => 'A new password has been sent to the e-mail address provided.'];
	}

	function change(ChangePasswordRequest $request)
	{
		if (Hash::check($request->input('password_current'), $request->user()->password)) {
			try {
				User::where(['email' => $request->user()->email])->update(['password' => Hash::make($request->input('password'))]);
			} catch (Exception $e) {
				Log::error($e->getMessage());
				throw new Exception("Database error.", 402);
			}
		} else {
			throw new Exception("Invalid current password.", 402);
		}

		return ['message' => 'A password has been updated.'];
	}

	function delete(Request $request)
	{
		$request->user()->tokens()->delete();
		return ['message' => 'Tokens has been removed.'];
	}
}