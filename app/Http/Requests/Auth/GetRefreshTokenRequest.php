<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Port\Core\Auth\GetRefreshTokenPort;

class GetRefreshTokenRequest extends BaseRequest implements GetRefreshTokenPort
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
            'refreshToken' => ['required', 'string'],
        ];
    }

    public function getRefreshToken(): string
    {
        return $this->input('refreshToken');
    }
}
