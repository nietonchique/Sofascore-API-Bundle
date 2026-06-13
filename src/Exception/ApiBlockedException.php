<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Exception;

/**
 * Thrown when the API answers with HTTP 403 (typically a Cloudflare block).
 *
 * The {@see \Nietonchique\SofascoreApiBundle\Transport\ChainTransport} catches this
 * exception to fall back from the plain HTTP transport to the headless-Chrome one.
 */
final class ApiBlockedException extends ApiException
{
}
