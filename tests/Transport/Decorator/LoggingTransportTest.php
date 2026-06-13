<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Transport\Decorator;

use Nietonchique\SofascoreApiBundle\Exception\ApiException;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use Nietonchique\SofascoreApiBundle\Transport\Decorator\LoggingTransport;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Stringable;

#[CoversClass(LoggingTransport::class)]
final class LoggingTransportTest extends TestCase
{
    public function testLogsDebugOnSuccess(): void
    {
        $logger = new CollectingLogger();
        $transport = new LoggingTransport(new MockTransport(['ok' => true]), $logger);

        self::assertSame(['ok' => true], $transport->get('/x'));
        self::assertContains('debug', array_column($logger->records, 'level'));
    }

    public function testLogsErrorAndRethrowsOnFailure(): void
    {
        $logger = new CollectingLogger();
        $inner = $this->createMock(TransportInterface::class);
        $inner->method('get')->willThrowException(new ApiException('boom', 500));
        $transport = new LoggingTransport($inner, $logger);

        try {
            $transport->get('/x');
            self::fail('Expected ApiException');
        } catch (ApiException) {
            self::assertContains('error', array_column($logger->records, 'level'));
        }
    }
}

/**
 * @internal
 */
final class CollectingLogger extends AbstractLogger
{
    /**
     * @var list<array{level: string, message: string}>
     */
    public array $records = [];

    /**
     * @param array<array-key, mixed> $context
     */
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        $this->records[] = [
            'level' => \is_string($level) ? $level : '',
            'message' => (string) $message,
        ];
    }
}
