<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

use Nietonchique\SofascoreApiBundle\Exception\InvalidArgumentException;

/**
 * Transfers endpoint group. Methods are a faithful port of the corresponding Python
 * `sofascore_wrapper` module.
 */
final class Transfers extends AbstractEndpoint
{
    /**
     * Fetches the transfer feed from Sofascore API with optional filters (Python {@code get_transfer_feed}).
     *
     * @return array<string, mixed>
     */
    public function getTransferFeed(
        int $page = 1,
        string $sortBy = 'transferdate',
        int $minAge = 15,
        int $maxAge = 50,
        ?string $nationality = null,
        ?int $uniqueTournamentId = null,
        ?string $position = null,
        ?int $followersCount = null,
    ): array {
        $sortMapping = [
            'followers' => '-userCount',
            'transferfee' => '-transferFee',
            'transferdate' => '-transferDate',
        ];
        $sortKey = strtolower($sortBy);
        if (!isset($sortMapping[$sortKey])) {
            throw new InvalidArgumentException(\sprintf('Invalid sort_by value: Must be one of %s', implode(', ', array_keys($sortMapping))));
        }
        $sortBy = $sortMapping[$sortKey];

        $minAge = max(15, min(50, $minAge));
        $maxAge = max(15, min(50, $maxAge));

        if (null !== $followersCount) {
            $followersCount = min(1000, max(0, $followersCount));
        }

        $validPositions = ['FW', 'MF', 'DF', 'GK'];
        if (null !== $position && '' !== $position) {
            $position = strtoupper($position);
            if (!\in_array($position, $validPositions, true)) {
                throw new InvalidArgumentException(\sprintf('Invalid position: %s. Must be one of %s', $position, implode(', ', $validPositions)));
            }
        } else {
            $position = null;
        }

        /** @var array<string, scalar|null> $query */
        $query = [
            'page' => $page,
            'sort' => $sortBy,
            'minAge' => $minAge,
            'maxAge' => $maxAge,
        ];

        if (null !== $nationality && '' !== $nationality) {
            $query['nationality'] = strtoupper($nationality);
        }
        if (null !== $uniqueTournamentId) {
            $query['uniqueTournamentId'] = $uniqueTournamentId;
        }
        if (null !== $position) {
            $query['position'] = $position;
        }
        if (null !== $followersCount && 0 !== $followersCount) {
            $query['followersCount'] = $followersCount;
        }

        /** @var array<string, mixed> $data */
        $data = $this->get('/transfer', $query);

        return $data;
    }
}
