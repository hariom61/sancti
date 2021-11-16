<?php

namespace Sancti\Exceptions;

use Throwable;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class SanctiCodeHandler extends ExceptionHandler
{
	// A list of the exception types that are not reported.
	protected $dontReport = [];

	// A list of the inputs that are never flashed for validation exceptions.
	protected $dontFlash = [
		'current_password',
		'password',
		'password_confirmation',
	];

	// Register the exception handling callbacks for the application.
	public function register()
	{
		$this->reportable(function (Throwable $e) {
			//
		});
	}


	public function render($request, Throwable $e)
	{
		// Only application/json in requests
		if(config('sancti.settings.force_json_response') == true) {
			return $this->handleApiException($request, $e);
		}

		// Add Accept: application/json in request
		if ($request->wantsJson()) {
			return $this->handleApiException($request, $e);
		} else {
			return parent::render($request, $e);
		}
	}

	private function handleApiException($request, Throwable $ex)
	{
		$ex = $this->prepareException($ex);

		if ($ex instanceof \Illuminate\Http\Exception\HttpResponseException) {
			$ex = $ex->getResponse();
		}

		if ($ex instanceof \Illuminate\Auth\AuthenticationException) {
			$ex = $this->unauthenticated($request, $ex);
		}

		if ($ex instanceof \Illuminate\Validation\ValidationException) {
			$ex = $this->convertValidationExceptionToResponse($ex, $request);
		}

		return $this->customApiResponse($ex);
	}

	private function customApiResponse($ex)
	{
		$code = (int) $ex->getCode();

		if (method_exists($ex, 'getStatusCode')) {
			$code = (int) $ex->getStatusCode();
		}

		if($code < 400) {
			$code = 422;
		}

		$res = [];

		switch ($code) {
			case 401:
				$res['message'] = 'Unauthorized';
				break;
			case 402:
				$res['message'] = 'Payment required';
				break;
			case 403:
				$res['message'] = 'Forbidden';
				break;
			case 404:
				$res['message'] = 'Not Found';
				break;
			case 405:
				$res['message'] = 'Method Not Allowed';
				break;
			case 406:
				$res['message'] = 'Not Acceptable';
				break;
			case 429:
				$res['message'] = 'Too many requests';
				break;
			default:
				$res['message'] = $ex->getMessage();
				break;
		}

		if (config('app.debug')) {
			$res['code'] = $code;

			if (config('sancti.settings.debug') == true) {
				$res['ex'] = get_class($ex);
				$res['file'] = $ex->getFile();
				$res['line'] = $ex->getLine();
				$res['trace'] = $ex->getTrace();
			}
		}

		return response()->json($res, $code);
	}
}