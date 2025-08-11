<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'email' => 'required',
            'password' => [
                'required',
            ],
        ];
    }

        public function failedValidation(Validator $validator)
{
    throw new HttpResponseException(response()->json([
        'status' => false,
        'message' => 'Validation error',
        'errors' => $validator->errors()
    ], 422));
}
}
