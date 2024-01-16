<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Core\Query\Enum\OrderDirection;
use App\Core\User\Query\UserOrderBy;
use App\Http\Requests\BaseRequest;
use App\Models\User\User;
use App\Port\Core\User\GetAllUserPort;
use App\Rules\Enum\BackedEnumRule;

class GetAllUserRequest extends BaseRequest implements GetAllUserPort
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->getAuthenticatedUser()->can('see-all', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'order_by' => ['bail', 'nullable', 'string', new BackedEnumRule(UserOrderBy::NAME)],
            'order_dir' => ['bail', 'nullable', 'string', new BackedEnumRule(OrderDirection::ASCENDING)],
            'page' => ['bail', 'nullable', 'integer'],
            'per_page' => ['bail', 'nullable', 'integer'],
        ];
    }

    public function getUserActor(): User
    {
        return $this->getAuthenticatedUser();
    }

    public function getOrderBy(): ?UserOrderBy
    {
        return UserOrderBy::tryFrom((string)$this->input('order_by'));
    }

    public function getOrderDirection(): ?OrderDirection
    {
        return OrderDirection::tryFrom((string)$this->input('order_dir'));
    }

    public function getPage(): ?int
    {
        return is_null($this->input('page'))
            ? null
            : intval($this->input('page'));
    }

    public function getPerPage(): ?int
    {
        return is_null($this->input('per_page'))
            ? null
            : intval($this->input('per_page'));
    }
}
