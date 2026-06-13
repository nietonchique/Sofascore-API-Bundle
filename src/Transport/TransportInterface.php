<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Transport;

use Nietonchique\SofascoreApiBundle\Exception\ApiException;

/**
 * Low-level contract for talking to the SofaScore JSON API.
 *
 * Every endpoint class depends only on this interface, which makes the whole
 * library transport-agnostic and trivially mockable in tests.
 */
interface TransportInterface
{
    /**
     * Perform a GET request against an API endpoint (relative to the base URL)
     * and return the decoded JSON body.
     *
     * @param string                     $endpoint path beginning with "/", e.g. "/event/12345/h2h"
     * @param array<string, scalar|null> $query    query-string parameters
     *
     * @return array<array-key, mixed>
     *
     * @throws ApiException on any non-2xx response or undecodable body
     */
    public function get(string $endpoint, array $query = []): array;

    /**
     * Perform a GET request against a fully-qualified URL and return the decoded
     * JSON body. Mirrors the Python wrapper's {@code _raw_get}.
     *
     * @return array<array-key, mixed>
     *
     * @throws ApiException on any non-2xx response or undecodable body
     */
    public function getRaw(string $url): array;
}
