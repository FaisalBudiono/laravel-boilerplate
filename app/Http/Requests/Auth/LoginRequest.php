<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Port\Services\Auth\LoginPort;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest implements LoginPort
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
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function getEmail(): string
    {
        return $this->input('email') ?? '';
    }

    public function getUserPassword(): string
    {
        return $this->input('password') ?? '';
    }

    public function getUserAgent(): string
    {
        return $this->userAgent() ?? '';
    }

    public function getIPAdress(): string
    {
        return $this->ip() ?? '';
    }
}
