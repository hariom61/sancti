<?php

namespace Sancti\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
	protected $stopOnFirstFailure = true;

	public function authorize()
	{
		return true; // Allow all
	}

	public function rules()
	{
		$email = 'email:rfc,dns';
		if(env('APP_DEBUG') == true) {
			$email = 'email';
		}

		return [
			'email' => ['required', $email, 'max:191'],
			'password' => 'required|min:11',
		];
	}

	public function failedValidation(Validator $validator)
	{
		throw new \Exception($validator->errors()->first());
	}

	function prepareForValidation()
	{
		$this->merge(
			collect(request()->json()->all())->only(['email', 'password'])->toArray()
		);
	}
}
