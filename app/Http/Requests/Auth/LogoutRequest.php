<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Port\Services\Auth\LogoutPort;
use Illuminate\Foundation\Http\FormRequest;

class LogoutRequest extends FormRequest implements LogoutPort
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
            'refreshToken' => ['required', 'string'],
        ];
    }

    public function getRefreshToken(): string
    {
        return $this->input('refreshToken') ?? '';
    }
}
