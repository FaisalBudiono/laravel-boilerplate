<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use App\Models\User\User;
use App\Port\Core\User\GetUserPort;

class GetUserRequest extends BaseRequest implements GetUserPort
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
        return [];
    }

    public function getUserModel(): User
    {
        return $this->route('userID');
    }
}
