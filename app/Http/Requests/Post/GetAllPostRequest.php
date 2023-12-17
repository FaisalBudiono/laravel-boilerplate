<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\BaseRequest;
use App\Models\User\User;
use App\Port\Core\Post\GetAllPostPort;

class GetAllPostRequest extends BaseRequest implements GetAllPostPort
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->getAuthenticatedUser();

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
            'user_id' => ['integer'],
            'page' => ['integer'],
            'per_page' => ['integer'],
        ];
    }

    public function getUserActor(): User
    {
        return $this->getAuthenticatedUser();
    }

    public function getPerPage(): ?int
    {
        return $this->input('per_page');
    }

    public function getPage(): int
    {
        return $this->input('page') ?? 1;
    }

    public function getUserFilter(): ?User
    {
        $userID = $this->input('user_id');

        return is_null($userID) ? null : User::findByIDOrFail($userID);
    }
}
