<?php

declare(strict_types=1);

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
            'user' => ['integer'],
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
        $perPage = $this->input('per_page');

        return is_null($perPage)
            ? null
            : intval($perPage);
    }

    public function getPage(): int
    {
        $page = $this->input('page');

        return is_null($page)
            ? 1
            : intval($page);
    }

    public function getUserFilter(): ?User
    {
        $userID = $this->input('user');

        return is_null($userID)
            ? null
            : User::findByIDOrFail(intval($userID));
    }
}
