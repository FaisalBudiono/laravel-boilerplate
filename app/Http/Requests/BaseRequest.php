<?php

namespace App\Http\Requests;

use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Http\UnauthorizedException;
use App\Exceptions\Http\UnprocessableEntityException;
use App\Http\Middleware\XRequestIDMiddleware;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
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

    protected function failedValidation(Validator $validator)
    {
        throw new UnprocessableEntityException(new ExceptionMessageStandard(
            'Structure body/param might be invalid.',
            ExceptionErrorCode::INVALID_VALIDATION->value,
            $validator->errors()->jsonSerialize(),
        ));
    }

    protected function failedAuthorization()
    {
        throw new UnauthorizedException(new ExceptionMessageStandard(
            'Authorization is required to access this resource.',
            ExceptionErrorCode::REQUIRE_AUTHORIZATION->value,
        ));
    }

    protected function getLoggedInUserInstance(): User
    {
        $user = auth()->user();

        if (is_null($user)) {
            return new User;
        }

        return $user;
    }
}
