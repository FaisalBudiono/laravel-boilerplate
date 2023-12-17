<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\BaseRequest;
use App\Models\Permission\Enum\RoleName;
use App\Models\Post\Post;
use App\Models\User\User;
use App\Port\Core\Post\UpdatePostPort;

class UpdatePostRequest extends BaseRequest implements UpdatePostPort
{
    protected ?User $authenticatedUser = null;
    protected ?Post $postFromRoute = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->getAuthenticatedUser()->can('update', $this->getPostFromRoute());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:250'],
            'content' => ['nullable', 'string'],
        ];
    }

    public function getPost(): Post
    {
        return $this->getPostFromRoute();
    }

    public function getUserActor(): User
    {
        return $this->isAdmin($this->getAuthenticatedUser())
            ? $this->getPostFromRoute()->user
            : $this->getAuthenticatedUser();
    }

    public function getTitle(): string
    {
        return $this->input('title');
    }

    public function getPostContent(): ?string
    {
        return $this->input('content');
    }

    protected function getAuthenticatedUser(): User
    {
        if (is_null($this->authenticatedUser)) {
            $this->authenticatedUser = $this->getUserOrFail();
        }

        return $this->authenticatedUser;
    }

    protected function getPostFromRoute(): Post
    {
        if (is_null($this->postFromRoute)) {
            $this->postFromRoute = $this->route('postID');
        }

        return $this->postFromRoute;
    }

    protected function isAdmin(User $user): bool
    {
        return $user->roles->contains('name', RoleName::ADMIN);
    }
}
