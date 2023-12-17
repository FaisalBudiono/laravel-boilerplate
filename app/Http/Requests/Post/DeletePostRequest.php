<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\BaseRequest;
use App\Models\Post\Post;
use App\Models\User\User;
use App\Port\Core\Post\DeletePostPort;

class DeletePostRequest extends BaseRequest implements DeletePostPort
{
    protected ?User $authenticatedUser = null;
    protected ?Post $postFromRoute = null;

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

    protected function getPostFromRoute(): Post
    {
        if (is_null($this->postFromRoute)) {
            $this->postFromRoute = $this->route('postID');
        }

        return $this->postFromRoute;
    }

    protected function getAuthenticatedUser(): User
    {
        if (is_null($this->authenticatedUser)) {
            $this->authenticatedUser = $this->getUserOrFail();
        }

        return $this->authenticatedUser;
    }
}
