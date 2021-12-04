<?php

namespace Sancti\Exceptions;

use Throwable;
use Exception;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class SanctiHandler extends ExceptionHandler
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

		$this->renderable(function (MethodNotAllowedHttpException $e, $request) {
			if ($request->is('api/*')) {
				return response()->json(['message' => 'Method Not Allowed'], 405);
			}
		});

		$this->renderable(function (InvalidParameterException $e, $request) {
			if ($request->is('api/*')) {
				return response()->json(['message' => 'Invalid Parametr'], 422);
			}
		});

		$this->renderable(function (ResourceNotFoundException $e, $request) {
			if ($request->is('api/*')) {
				return response()->json(['message' => 'Resource Not Found'], 404);
			}
		});

		$this->renderable(function (RouteNotFoundException $e, $request) {
			if ($request->is('api/*')) {
				return response()->json(['message' => 'Route Not Found'], 404);
			}
		});

		$this->renderable(function (MissingMandatoryParametersException $e, $request) {
			if ($request->is('api/*')) {
				return response()->json(['message' => 'Missing Parameters'], 422);
			}
		});

		$this->renderable(function (HttpResponseException $e, $request) {
			if ($request->is('api/*')) {
				return response()->json(['message' => 'Http Response'], 422);
			}
		});

		$this->renderable(function (NotFoundHttpException $e, $request) {
			if ($request->is('api/*')) {
				return response()->json(['message' => 'Http Not Found'], 404);
			}
		});

		$this->renderable(function (PostTooLargeException $e, $request) {
			if ($request->is('api/*')) {
				return response()->json(['message' => 'Post Too Large'], 422);
			}
		});

		$this->renderable(function (ThrottleRequestsException $e, $request) {
			if ($request->is('api/*')) {
				return response()->json(['message' => 'Too Many Requests'], 429);
			}
		});

		$this->renderable(function (Exception $e, $request) {
			if ($request->is('api/*')) {
				$code = (int) $e->getCode();

				if($code < 400) {
					$code = 422;
				}

				$res['message'] = $e->getMessage();

				if (config('app.debug')) {
					if (config('sancti.settings.debug') == true) {
						$res['code'] = $code;
						$res['ex'] = get_class($e);
						$res['file'] = $e->getFile();
						$res['line'] = $e->getLine();
						$res['trace'] = $e->getTrace();
					}
				}

				return response()->json($res, $code);
			}
		});
	}

	public function render($request, Throwable $e)
	{
		// if ($e instanceof \Illuminate\Http\Exceptions\PostTooLargeException) {
		// 	// Redirect to url: /redirect/error
		// 	// then with errors back to upload form url: /galeries/create
		// 	// return redirect()->to(route('upload.error'));
		// }

		// Force an application/json rendering on API calls for error page
		// if ($request->is('api/*')) {
		// 	$request->headers->set('Accept', 'application/json');
		// 	return response()->json([
		// 		'error' => 'Unauthorized.',
		// 		'message' => __($e->getMessage()) ?? 'Invalid route'
		// 	], 402);
		// }

		return parent::render($request, $e);
	}
}