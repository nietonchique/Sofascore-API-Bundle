<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Exception;

/**
 * Thrown for invalid arguments passed to endpoint methods (e.g. an unknown sport
 * slug). Replaces the Python wrapper's {@code ValueError}.
 */
final class InvalidArgumentException extends \InvalidArgumentException implements SofascoreExceptionInterface
{
}
