<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;

/**
 * Manager endpoint group. Faithful port of the Python {@code manager.py} module;
 * the manager id is bound at construction.
 */
final class Manager extends AbstractEndpoint
{
    public function __construct(
        TransportInterface $transport,
        Enums $enums,
        private readonly int $managerId,
    ) {
        parent::__construct($transport, $enums);
    }

    /**
     * Detailed information about the manager (Python {@code get_manager}).
     *
     * @return array<string, mixed>
     */
    public function getManager(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/manager/{$this->managerId}");

        return $data;
    }
}
