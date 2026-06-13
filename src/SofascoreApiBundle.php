<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle;

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
use Nietonchique\SofascoreApiBundle\Transport\ChainTransport;
use Nietonchique\SofascoreApiBundle\Transport\ChromeBrowserFetcher;
use Nietonchique\SofascoreApiBundle\Transport\ChromeTransport;
use Nietonchique\SofascoreApiBundle\Transport\Decorator\CachingTransport;
use Nietonchique\SofascoreApiBundle\Transport\Decorator\LoggingTransport;
use Nietonchique\SofascoreApiBundle\Transport\Decorator\RateLimitedTransport;
use Nietonchique\SofascoreApiBundle\Transport\HttpClientTransport;
use Nietonchique\SofascoreApiBundle\Transport\TransportInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Retry\GenericRetryStrategy;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class SofascoreApiBundle extends AbstractBundle
{
    /**
     * Endpoint groups whose constructor needs no extra (id/query) argument and
     * can therefore be wired as autowirable services. Id-bearing groups
     * (match, player, team, …) are obtained through {@see SofascoreClient}.
     *
     * @var list<class-string>
     */
    private const AUTOWIRED_ENDPOINTS = [
        Transfers::class,
        News::class,
        UserData::class,
        AmericanFootball::class,
        Baseball::class,
        Basketball::class,
        Cricket::class,
        Esports::class,
        IceHockey::class,
        Mma::class,
        Motorsport::class,
        Rugby::class,
        Tennis::class,
    ];

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->enumNode('transport')
                    ->info('Which transport to use: plain HTTP, headless Chrome, or chain (HTTP then Chrome fallback).')
                    ->values(['http', 'chrome', 'chain'])
                    ->defaultValue('chain')
                ->end()
                ->scalarNode('base_url')->defaultValue(HttpClientTransport::BASE_URL)->cannotBeEmpty()->end()
                ->arrayNode('http')->addDefaultsIfNotSet()->children()
                    ->floatNode('timeout')->defaultValue(10.0)->end()
                    ->scalarNode('proxy')->defaultNull()->end()
                    ->arrayNode('headers')->scalarPrototype()->end()->end()
                ->end()->end()
                ->arrayNode('chrome')->addDefaultsIfNotSet()->children()
                    ->scalarNode('binary')->defaultValue('google-chrome-stable')->end()
                    ->booleanNode('headless')->defaultTrue()->end()
                    ->integerNode('timeout_ms')->defaultValue(30_000)->end()
                ->end()->end()
                ->arrayNode('retry')->addDefaultsIfNotSet()->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->integerNode('max_retries')->defaultValue(3)->end()
                    ->integerNode('delay_ms')->defaultValue(1_000)->end()
                ->end()->end()
                ->arrayNode('cache')->addDefaultsIfNotSet()->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->scalarNode('pool')->defaultValue('cache.app')->end()
                    ->integerNode('ttl')->defaultValue(300)->end()
                ->end()->end()
                ->arrayNode('rate_limit')->addDefaultsIfNotSet()->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->integerNode('limit')->defaultValue(60)->end()
                    ->scalarNode('interval')->defaultValue('1 minute')->end()
                ->end()->end()
                ->arrayNode('logging')->addDefaultsIfNotSet()->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->scalarNode('service')->defaultValue('logger')->end()
                ->end()->end()
            ->end();
    }

    /**
     * @param array<array-key, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();
        $p = 'sofascore_api';

        // The config is validated/defaulted by configure(); narrow each section
        // defensively so the static analyser stays happy without unsafe casts.
        $http = \is_array($config['http'] ?? null) ? $config['http'] : [];
        $chrome = \is_array($config['chrome'] ?? null) ? $config['chrome'] : [];
        $retry = \is_array($config['retry'] ?? null) ? $config['retry'] : [];
        $cache = \is_array($config['cache'] ?? null) ? $config['cache'] : [];
        $rateLimit = \is_array($config['rate_limit'] ?? null) ? $config['rate_limit'] : [];
        $logging = \is_array($config['logging'] ?? null) ? $config['logging'] : [];
        $baseUrl = $config['base_url'] ?? HttpClientTransport::BASE_URL;
        $headers = \is_array($http['headers'] ?? null) ? $http['headers'] : [];

        $services->set(Enums::class)->public();

        // --- HTTP client (optionally retry-wrapped) ---
        $clientOptions = ['timeout' => $http['timeout'] ?? 10.0];
        if (null !== ($http['proxy'] ?? null)) {
            $clientOptions['proxy'] = $http['proxy'];
        }
        $services->set($p.'.http_client.inner', HttpClientInterface::class)
            ->factory([HttpClient::class, 'create'])
            ->args([$clientOptions]);
        $clientId = $p.'.http_client.inner';

        if (true === ($retry['enabled'] ?? false)) {
            $services->set($p.'.retry_strategy', GenericRetryStrategy::class)
                ->arg('$delayMs', $retry['delay_ms'] ?? 1000);
            $services->set($p.'.http_client', RetryableHttpClient::class)
                ->args([service($clientId), service($p.'.retry_strategy'), $retry['max_retries'] ?? 3]);
            $clientId = $p.'.http_client';
        }

        // --- base transports ---
        $services->set($p.'.transport.http', HttpClientTransport::class)
            ->args([service($clientId), $baseUrl, $headers]);

        $services->set($p.'.chrome_fetcher', ChromeBrowserFetcher::class)
            ->args([$chrome['binary'] ?? 'google-chrome-stable', $chrome['headless'] ?? true, $chrome['timeout_ms'] ?? 30000]);
        $services->set($p.'.transport.chrome', ChromeTransport::class)
            ->args([service($p.'.chrome_fetcher'), $baseUrl]);

        $services->set($p.'.transport.chain', ChainTransport::class)
            ->args([service($p.'.transport.http'), service($p.'.transport.chrome')]);

        $currentId = match ($config['transport'] ?? 'chain') {
            'http' => $p.'.transport.http',
            'chrome' => $p.'.transport.chrome',
            default => $p.'.transport.chain',
        };

        // --- opt-in decorators: innermost = core, wrapped logging -> rate_limit -> cache ---
        if (true === ($logging['enabled'] ?? false)) {
            $loggerService = \is_string($logging['service'] ?? null) ? $logging['service'] : 'logger';
            $services->set($p.'.transport.logging', LoggingTransport::class)
                ->args([service($currentId), service($loggerService)]);
            $currentId = $p.'.transport.logging';
        }

        if (true === ($rateLimit['enabled'] ?? false)) {
            $services->set($p.'.rate_limiter.storage', InMemoryStorage::class);
            $services->set($p.'.rate_limiter.factory', RateLimiterFactory::class)
                ->args([
                    [
                        'id' => 'sofascore_api',
                        // token_bucket so the transport decorator can reserve()/wait().
                        'policy' => 'token_bucket',
                        'limit' => $rateLimit['limit'] ?? 60,
                        'rate' => [
                            'interval' => $rateLimit['interval'] ?? '1 minute',
                            'amount' => $rateLimit['limit'] ?? 60,
                        ],
                    ],
                    service($p.'.rate_limiter.storage'),
                ]);
            $services->set($p.'.rate_limiter', \Symfony\Component\RateLimiter\LimiterInterface::class)
                ->factory([service($p.'.rate_limiter.factory'), 'create'])
                ->args([null]);
            $services->set($p.'.transport.rate_limited', RateLimitedTransport::class)
                ->args([service($currentId), service($p.'.rate_limiter')]);
            $currentId = $p.'.transport.rate_limited';
        }

        if (true === ($cache['enabled'] ?? false)) {
            $pool = \is_string($cache['pool'] ?? null) ? $cache['pool'] : 'cache.app';
            $services->set($p.'.transport.cache', CachingTransport::class)
                ->args([service($currentId), service($pool), $cache['ttl'] ?? 300]);
            $currentId = $p.'.transport.cache';
        }

        $services->alias(TransportInterface::class, $currentId)->public();
        $services->alias($p.'.transport', $currentId)->public();

        // --- client + autowirable endpoint groups ---
        $services->set(SofascoreClient::class)
            ->args([service(TransportInterface::class), service(Enums::class)])
            ->public();

        foreach (self::AUTOWIRED_ENDPOINTS as $class) {
            $services->set($class)
                ->args([service(TransportInterface::class), service(Enums::class)])
                ->public();
        }
    }
}
