<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\DependencyInjection;

use Nietonchique\SofascoreApiBundle\Endpoint\AmericanFootball;
use Nietonchique\SofascoreApiBundle\Endpoint\Baseball;
use Nietonchique\SofascoreApiBundle\Endpoint\Basketball;
use Nietonchique\SofascoreApiBundle\Endpoint\Cricket;
use Nietonchique\SofascoreApiBundle\Endpoint\Esports;
use Nietonchique\SofascoreApiBundle\Endpoint\IceHockey;
use Nietonchique\SofascoreApiBundle\Endpoint\Mma;
use Nietonchique\SofascoreApiBundle\Endpoint\Motorsport;
use Nietonchique\SofascoreApiBundle\Endpoint\News;
use Nietonchique\SofascoreApiBundle\Endpoint\Rugby;
use Nietonchique\SofascoreApiBundle\Endpoint\Tennis;
use Nietonchique\SofascoreApiBundle\Endpoint\Transfers;
use Nietonchique\SofascoreApiBundle\Endpoint\UserData;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\SofascoreClient;
use Nietonchique\SofascoreApiBundle\Transport\ChainTransport;
use Nietonchique\SofascoreApiBundle\Transport\ChromeTransport;
use Nietonchique\SofascoreApiBundle\Transport\Decorator\LoggingTransport;
use Nietonchique\SofascoreApiBundle\Transport\HttpClientTransport;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
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

    public function testHttpHeadersPreserveBrowserHeaderNames(): void
    {
        $transport = $this->boot([
            'transport' => 'http',
            'http' => [
                'headers' => [
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'sec-ch-ua' => '"Chromium";v="148"',
                ],
            ],
        ])->get(TransportInterface::class);

        self::assertInstanceOf(HttpClientTransport::class, $transport);

        $headersProperty = new ReflectionProperty($transport, 'headers');
        $headers = $headersProperty->getValue($transport);

        self::assertIsArray($headers);
        self::assertArrayHasKey('Accept-Language', $headers);
        self::assertArrayHasKey('sec-ch-ua', $headers);
        self::assertArrayNotHasKey('Accept_Language', $headers);
        self::assertArrayNotHasKey('sec_ch_ua', $headers);
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
     * @return iterable<string, array{class-string}>
     */
    public static function autowiredEndpoints(): iterable
    {
        yield 'transfers' => [Transfers::class];
        yield 'news' => [News::class];
        yield 'user-data' => [UserData::class];
        yield 'american-football' => [AmericanFootball::class];
        yield 'baseball' => [Baseball::class];
        yield 'cricket' => [Cricket::class];
        yield 'esports' => [Esports::class];
        yield 'ice-hockey' => [IceHockey::class];
        yield 'mma' => [Mma::class];
        yield 'motorsport' => [Motorsport::class];
        yield 'rugby' => [Rugby::class];
        yield 'tennis' => [Tennis::class];
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('autowiredEndpoints')]
    public function testEveryNoArgEndpointIsAutowired(string $class): void
    {
        self::assertInstanceOf($class, $this->boot()->get($class));
    }

    public function testChromeProxyConfigCompiles(): void
    {
        $container = $this->boot([
            'transport' => 'chrome',
            'chrome' => ['proxy' => 'socks5://127.0.0.1:1080', 'warmup_url' => null],
        ]);

        self::assertInstanceOf(ChromeTransport::class, $container->get(TransportInterface::class));
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function invalidConfigs(): iterable
    {
        yield 'negative timeout' => [['http' => ['timeout' => -1.0]]];
        yield 'negative ttl' => [['cache' => ['ttl' => -5]]];
        yield 'negative max_retries' => [['retry' => ['max_retries' => -1]]];
        yield 'zero rate limit' => [['rate_limit' => ['limit' => 0]]];
        yield 'unknown transport' => [['transport' => 'carrier-pigeon']];
        yield 'empty base_url' => [['base_url' => '']];
    }

    /**
     * @param array<string, mixed> $config
     */
    #[DataProvider('invalidConfigs')]
    public function testInvalidConfigurationIsRejected(array $config): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->boot($config);
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
