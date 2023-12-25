<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Port\Core\Auth\LoginPort;

class LoginRequest extends BaseRequest implements LoginPort
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:250',
            ],
            'password' => [
                'required',
            ],
        ];
    }

    public function getUserEmail(): string
    {
        return $this->input('email');
    }

    public function getUserPassword(): string
    {
        return $this->input('password');
    }
}
