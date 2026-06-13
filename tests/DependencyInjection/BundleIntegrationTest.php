<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\DependencyInjection;

use Nietonchique\SofascoreApiBundle\Endpoint\Basketball;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\SofascoreClient;
use Nietonchique\SofascoreApiBundle\Transport\ChainTransport;
use Nietonchique\SofascoreApiBundle\Transport\Decorator\LoggingTransport;
use Nietonchique\SofascoreApiBundle\Transport\HttpClientTransport;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class BundleIntegrationTest extends TestCase
{
    public function testDefaultWiringResolvesClientAndEndpoints(): void
    {
        $container = $this->boot();

        $client = $container->get(SofascoreClient::class);
        self::assertInstanceOf(SofascoreClient::class, $client);
        self::assertInstanceOf(Basketball::class, $container->get(Basketball::class));
        self::assertInstanceOf(Enums::class, $container->get(Enums::class));
    }

    public function testDefaultTransportIsChain(): void
    {
        $container = $this->boot();

        self::assertInstanceOf(ChainTransport::class, $container->get(TransportInterface::class));
    }

    public function testHttpTransportModeSelectsHttpClientTransport(): void
    {
        $container = $this->boot(['transport' => 'http']);

        self::assertInstanceOf(HttpClientTransport::class, $container->get(TransportInterface::class));
    }

    public function testLoggingDecoratorIsWiredWhenEnabled(): void
    {
        $container = $this->boot([
            'transport' => 'http',
            'logging' => ['enabled' => true, 'service' => 'logger'],
        ]);

        self::assertInstanceOf(LoggingTransport::class, $container->get(TransportInterface::class));
    }

    public function testCacheDecoratorIsWiredWhenEnabled(): void
    {
        $container = $this->boot([
            'transport' => 'http',
            'cache' => ['enabled' => true, 'pool' => 'cache.app', 'ttl' => 60],
        ]);

        self::assertInstanceOf(
            \Nietonchique\SofascoreApiBundle\Transport\Decorator\CachingTransport::class,
            $container->get(TransportInterface::class),
        );
    }

    /**
     * @var list<TestKernel>
     */
    private array $kernels = [];

    protected function tearDown(): void
    {
        $filesystem = new Filesystem();
        foreach ($this->kernels as $kernel) {
            $kernel->shutdown();
            $filesystem->remove($kernel->getCacheDir());
        }
        $this->kernels = [];

        // Booting the kernel registers Symfony's exception handler; restore it
        // so PHPUnit does not flag the test as risky.
        restore_exception_handler();
    }

    /**
     * @param array<string, mixed> $config
     */
    private function boot(array $config = []): \Psr\Container\ContainerInterface
    {
        $kernel = new TestKernel($config);
        $kernel->boot();
        $this->kernels[] = $kernel;

        return $kernel->getContainer();
    }
}
