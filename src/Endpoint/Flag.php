<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;

/**
 * Flag (country flag image) endpoint group. Faithful port of the Python
 * {@code flag.py} module; the flag code is bound at construction.
 */
final class Flag extends AbstractEndpoint
{
    public function __construct(
        TransportInterface $transport,
        Enums $enums,
        private readonly string $flagCode,
    ) {
        parent::__construct($transport, $enums);
    }

    /**
     * URL of the flag image for the specified country code (Python {@code image}).
     *
     * No API call is performed; the URL is built from the (lower-cased) flag code,
     * mirroring the Python {@code flag_code.lower()} normalisation.
     */
    public function image(): string
    {
        return 'https://www.sofascore.com/static/images/flags/'.strtolower($this->flagCode).'.png';
    }
}
