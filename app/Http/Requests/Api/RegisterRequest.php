<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // dd('here');
        return [
            'name' => 'required|string|min:2|max:255',
            'firstname' => 'required|string|min:2|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5|confirmed',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Surname is required',
            'name.min' => 'Surname must be more then 1 character',
            'firstname.min' => 'First name must be more then 1 character',
            'firstname.required' => 'First name is required',
            'email.required' => 'Email address is required',
            'email.email' => 'Use an appropriate email address',
            'password.min' => 'Password must be more than 5 characters',
            'password.confirmed' => 'Passwords do not match',
        ];
    }
}
