<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Transport\Decorator;

use Nietonchique\SofascoreApiBundle\Exception\SofascoreExceptionInterface;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;
use Psr\Log\LoggerInterface;

/**
 * Opt-in PSR-3 logging decorator. Logs every request at debug level and every
 * failure at error level. Disabled by default.
 */
final class LoggingTransport implements TransportInterface
{
    public function __construct(
        private readonly TransportInterface $inner,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function get(string $endpoint, array $query = []): array
    {
        $this->logger->debug('SofaScore GET {endpoint}', ['endpoint' => $endpoint, 'query' => $query]);

        try {
            return $this->inner->get($endpoint, $query);
        } catch (SofascoreExceptionInterface $e) {
            $this->logger->error('SofaScore GET {endpoint} failed: {message}', [
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function getRaw(string $url): array
    {
        $this->logger->debug('SofaScore GET (raw) {url}', ['url' => $url]);

        try {
            return $this->inner->getRaw($url);
        } catch (SofascoreExceptionInterface $e) {
            $this->logger->error('SofaScore GET (raw) {url} failed: {message}', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
