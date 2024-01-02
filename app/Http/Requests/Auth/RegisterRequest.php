<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Port\Core\User\CreateUserPort;

class RegisterRequest extends BaseRequest implements CreateUserPort
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
            'email' => ['required', 'email', 'max:250'],
            'name' => ['required', 'string', 'max:250'],
            'password' => ['required', 'string'],
        ];
    }

    public function getName(): string
    {
        return $this->input('name');
    }

    public function getEmail(): string
    {
        return $this->input('email');
    }

    public function getUserPassword(): string
    {
        return $this->input('password');
    }
}
