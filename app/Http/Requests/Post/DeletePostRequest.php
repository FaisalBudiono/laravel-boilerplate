<?php

declare(strict_types=1);

namespace App\Http\Requests\Post;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Traits\PostFromRouteTrait;
use App\Models\Post\Post;
use App\Models\User\User;
use App\Port\Core\Post\DeletePostPort;

class DeletePostRequest extends BaseRequest implements DeletePostPort
{
    use PostFromRouteTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->getAuthenticatedUser()->can('delete', $this->getPostFromRoute());
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

    public function getPost(): Post
    {
        return $this->getPostFromRoute();
    }

    public function getUserActor(): User
    {
        return $this->getAuthenticatedUser();
    }
}
