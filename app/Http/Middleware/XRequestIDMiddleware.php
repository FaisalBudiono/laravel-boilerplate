<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Formatter\Randomizer\Randomizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XRequestIDMiddleware
{
    public const HEADER_NAME = 'X-Request-Id';

    public function __construct(protected Randomizer $randomizer)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestID = $this->randomizer->getRandomizeString();
        $request->headers->set(self::HEADER_NAME, $requestID);

        $response = $next($request);
        $response->headers->set(self::HEADER_NAME, $requestID);

        return $response;
    }
}
