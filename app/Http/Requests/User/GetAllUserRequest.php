<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Core\Query\Enum\OrderDirection;
use App\Core\User\Query\UserOrderBy;
use App\Http\Requests\BaseRequest;
use App\Port\Core\User\GetAllUserPort;
use App\Rules\Enum\BackedEnumRule;

class GetAllUserRequest extends BaseRequest implements GetAllUserPort
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
        return [
            'orderBy' => ['bail', 'string', new BackedEnumRule(UserOrderBy::NAME)],
            'orderDir' => ['bail', 'string', new BackedEnumRule(OrderDirection::ASCENDING)],
            'page' => ['bail', 'integer'],
            'perPage' => ['bail', 'integer'],
        ];
    }

    public function getOrderBy(): ?UserOrderBy
    {
        return UserOrderBy::tryFrom((string)$this->input('orderBy'));
    }

    public function getOrderDirection(): ?OrderDirection
    {
        return OrderDirection::tryFrom((string)$this->input('orderDir'));
    }

    public function getPage(): ?int
    {
        return is_null($this->input('page'))
            ? null
            : intval($this->input('page'));
    }

    public function getPerPage(): ?int
    {
        return is_null($this->input('perPage'))
            ? null
            : intval($this->input('perPage'));
    }
}
