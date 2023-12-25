<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Http\ForbiddenException;
use App\Exceptions\Http\UnauthorizedException;
use App\Exceptions\Http\UnprocessableEntityException;
use App\Http\Middleware\XRequestIDMiddleware;
use App\Models\User\User;
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

    public function getXRequestID(): string
    {
        return $this->header(XRequestIDMiddleware::HEADER_NAME, '');
    }

    protected function getUserOrFail(): User
    {
        $user = auth()->user();

        if (is_null($user)) {
            $this->failedAuthentication();
        }

        return $user;
    }

    protected function failedAuthentication()
    {
        throw new UnauthorizedException(new ExceptionMessageStandard(
            'Authentication is needed',
            ExceptionErrorCode::REQUIRE_AUTHORIZATION->value,
        ));
    }

    protected function failedAuthorization()
    {
        throw new ForbiddenException(new ExceptionMessageStandard(
            'Lack of authorization to access this resource',
            ExceptionErrorCode::LACK_OF_AUTHORIZATION->value,
        ));
    }

    protected function failedValidation(Validator $validator)
    {
        throw new UnprocessableEntityException(new ExceptionMessageStandard(
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
        $user = auth()->user();

        if (is_null($user)) {
            return new User();
        }

        return $user;
    }
}
