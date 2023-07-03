<?php

namespace App\Http\Requests\Healthcheck;

use App\Http\Requests\BaseRequest;
use App\Port\Core\Healthcheck\GetHealthcheckPort;

class HealthcheckRequest extends BaseRequest implements GetHealthcheckPort
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
}
