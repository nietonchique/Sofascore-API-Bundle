<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Exception;

use RuntimeException;
use Throwable;

/**
 * Thrown when the SofaScore API returns an unexpected (non-2xx) response or an
 * undecodable body. More specific subclasses are thrown for 403 and 404.
 */
class ApiException extends RuntimeException implements SofascoreExceptionInterface
{
    public function __construct(
        string $message,
        private readonly int $statusCode = 0,
        private readonly ?string $url = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
}
