<?php

declare(strict_types=1);

namespace App\Providers\ModelBinding;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Exceptions\Http\InternalServerErrorException;
use App\Exceptions\Http\NotFoundException;
use App\Exceptions\Models\ModelNotFoundException;
use App\Models\Post\Post;
use Illuminate\Support\Facades\Route;

class ModelBindingPost implements ModelBinding
{
    public function bindModel(): void
    {
        Route::bind('postID', function ($value) {
            try {
                return Post::findByIDOrFail(intval($value));
            } catch (ModelNotFoundException $e) {
                throw new NotFoundException($e->exceptionMessage, $e);
            } catch (\Throwable $e) {
                throw new InternalServerErrorException(new ExceptionMessageGeneric(), $e);
            }
        });
    }

    public function registerPattern(): void
    {
        Route::pattern('postID', '[0-9]+');
    }
}
