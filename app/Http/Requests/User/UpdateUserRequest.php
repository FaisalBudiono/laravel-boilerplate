<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Traits\UserFromRouteTrait;
use App\Models\User\User;
use App\Port\Core\User\UpdateUserPort;

class UpdateUserRequest extends BaseRequest implements UpdateUserPort
{
    use UserFromRouteTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->getAuthenticatedUser()->can('update', $this->getUserFromRouteUserID());
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

    public function getUserActor(): User
    {
        return $this->getAuthenticatedUser();
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

    public function getUserModel(): User
    {
        return $this->getUserFromRouteUserID();
    }
}
