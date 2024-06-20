<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'cpf' => ['string', 'min:11', 'max:11', 'unique:users,cpf'],
            'cnpj' => ['string', 'min:14', 'max:14', 'unique:users,cnpj'],
            'email' => ['bail', 'required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:100'],
        ];
    }
}
