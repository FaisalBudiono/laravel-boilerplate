<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\BaseRequest;
use App\Models\User\User;
use App\Port\Core\Post\CreatePostPort;

class CreatePostRequest extends BaseRequest implements CreatePostPort
{
    protected ?User $authenticatedUser = null;

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
            'title' => ['required', 'string', 'max:250'],
            'content' => ['nullable', 'string'],
        ];
    }

    public function getTitle(): string
    {
        return $this->input('title');
    }

    public function getPostContent(): ?string
    {
        return $this->input('content');
    }

    public function getUserActor(): User
    {
        return $this->getAuthenticatedUser();
    }

    protected function getAuthenticatedUser(): User
    {
        if (is_null($this->authenticatedUser)) {
            $this->authenticatedUser = $this->getUserOrFail();
        }

        return $this->authenticatedUser;
    }
}
