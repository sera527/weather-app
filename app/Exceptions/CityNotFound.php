<?php
declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class CityNotFound extends HttpException
{
    public function __construct(
        int $statusCode = 404,
        string $message = 'City not found.',
        ?\Throwable $previous = null,
        array $headers = [],
        int $code = 0,
    ) {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }
}
