<?php

namespace Sancti\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
	protected $stopOnFirstFailure = true;

	public function authorize()
	{
		return true; // Allow all
	}

	public function rules()
	{
		return [
			'password_current' => 'required',
			'password' => 'required|min:11|confirmed',
			'password_confirmation' => 'required'
		];
	}

	public function failedValidation(Validator $validator)
	{
		throw new \Exception($validator->errors()->first());
	}

	function prepareForValidation()
	{
		$this->merge(
			request()->json()->all()
		);
	}
}
