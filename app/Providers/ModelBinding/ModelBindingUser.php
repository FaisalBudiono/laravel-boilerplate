<?php

namespace App\Providers\ModelBinding;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Exceptions\Http\InternalServerErrorException;
use App\Exceptions\Http\NotFoundException;
use App\Exceptions\Models\ModelNotFoundException;
use App\Models\User\User;
use Exception;
use Illuminate\Support\Facades\Route;

class ModelBindingUser implements ModelBinding
{
    public function bindModel(): void
    {
        Route::bind('userID', function ($value) {
            try {
                return User::findByIdOrFail($value);
            } catch (ModelNotFoundException $e) {
                throw new NotFoundException($e->exceptionMessage);
            } catch (Exception $e) {
                throw new InternalServerErrorException(new ExceptionMessageGeneric);
            }
        });
    }

    public function registerPattern(): void
    {
        Route::pattern('userID', '[0-9]+');
    }
}
