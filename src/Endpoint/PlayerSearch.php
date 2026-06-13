<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;

/**
 * Player search endpoint group (the {@code PlayerSearch} class of the Python
 * {@code player.py} module); the query is bound at construction.
 */
final class PlayerSearch extends AbstractEndpoint
{
    public function __construct(
        TransportInterface $transport,
        Enums $enums,
        private readonly string $query,
    ) {
        parent::__construct($transport, $enums);
    }

    /**
     * Perform a dedicated player search (Python {@code search_player}). Mirrors
     * the Python {@code query.lower().replace(" ", "%20")} normalisation applied
     * before interpolation into the path.
     *
     * @return array<string, mixed>
     */
    public function searchPlayer(): array
    {
        $query = str_replace(' ', '%20', strtolower($this->query));

        /** @var array<string, mixed> $data */
        $data = $this->get("/search/players/{$query}");

        return $data;
    }
}
