<?php

namespace Sancti\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sancti\Http\Requests\LoginRequest;
use Sancti\Http\Requests\ActivateRequest;
use Sancti\Http\Requests\RegisterRequest;
use Sancti\Http\Requests\ResetPasswordRequest;
use Sancti\Http\Requests\ChangePasswordRequest;
use Sancti\Mail\RegisterMail;
use Sancti\Services\Sancti;

class SanctiController extends Controller
{
	function login(LoginRequest $r)
	{
		return (new Sancti())->login($r);
	}

	function activate(ActivateRequest $request)
	{
		return (new Sancti())->activate($request);
	}

	function register(RegisterRequest $r)
	{
		return (new Sancti())->register($r);
	}

	function reset(ResetPasswordRequest $r)
	{
		return (new Sancti())->reset($r);
	}

	function change(ChangePasswordRequest $r)
	{
		return (new Sancti())->change($r);
	}

	function logout(Request $r)
	{
		return (new Sancti())->logout($r);
	}

	function delete(Request $r)
	{
		return (new Sancti())->delete($r);
	}

	function user(Request $r)
	{
		return ['user' => $r->user()];
	}
}