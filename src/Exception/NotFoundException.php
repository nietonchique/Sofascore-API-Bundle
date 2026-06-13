<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Exception;

/**
 * Thrown when the API answers with HTTP 404 (unknown entity / endpoint).
 */
final class NotFoundException extends ApiException
{
}
