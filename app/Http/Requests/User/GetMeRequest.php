<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use App\Models\User\User;
use App\Port\Services\User\GetDetailPort;

class GetMeRequest extends BaseRequest implements GetDetailPort
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->getUserOrFail();

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }

    public function getLoggedInUser(): User
    {
        return $this->getUserOrFail();
    }
}
