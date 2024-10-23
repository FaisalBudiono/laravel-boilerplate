<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Exceptions\Http\Forbidden;
use App\Exceptions\Http\Unauthorized;
use App\Exceptions\Http\UnprocessableEntity;
use App\Models\User\User;
use App\Services\Auth\Exceptions\AuthErrorCode;
use App\Services\Formatter\ExceptionErrorCode;
use App\Services\Formatter\ExceptionMessage\ExceptionMessageStandard;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
    protected ?User $authenticatedUser = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    abstract public function authorize(): bool;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    abstract public function rules(): array;

    public function getEndpointInfo(): string
    {
        return $this->method() . ' ' . $this->url();
    }

    protected function getUserOrFail(): User
    {
        $user = auth()->guard()->user();

        if (is_null($user)) {
            $this->failedAuthentication();
        }
        assert($user instanceof User);

        return $user;
    }

    protected function failedAuthentication()
    {
        throw new Unauthorized(new ExceptionMessageStandard(
            'Authentication is needed',
            AuthErrorCode::REQUIRE_AUTHORIZATION->value,
        ));
    }

    protected function failedAuthorization()
    {
        throw new Forbidden(new ExceptionMessageStandard(
            'Lack of authorization to access this resource',
            AuthErrorCode::LACK_OF_AUTHORIZATION->value,
        ));
    }

    protected function failedValidation(Validator $validator)
    {
        throw new UnprocessableEntity(new ExceptionMessageStandard(
            'Structure body/param might be invalid.',
            ExceptionErrorCode::INVALID_VALIDATION->value,
            $validator->errors()->jsonSerialize(),
        ));
    }

    protected function getAuthenticatedUser(): User
    {
        if (is_null($this->authenticatedUser)) {
            $this->authenticatedUser = $this->getUserOrFail();
        }

        return $this->authenticatedUser;
    }

    protected function getLoggedInUserInstance(): User
    {
        $user = auth()->guard()->user();

        if (is_null($user)) {
            return new User();
        }
        assert($user instanceof User);

        return $user;
    }
}
