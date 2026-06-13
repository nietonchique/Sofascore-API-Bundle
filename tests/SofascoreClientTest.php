<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests;

use Nietonchique\SofascoreApiBundle\Endpoint\AbstractEndpoint;
use Nietonchique\SofascoreApiBundle\SofascoreClient;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use Nietonchique\SofascoreApiBundle\Transport\HttpClientTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

#[CoversClass(SofascoreClient::class)]
final class SofascoreClientTest extends TestCase
{
    /**
     * Every factory method must return a configured endpoint group. Reflection
     * keeps this honest as endpoints are added.
     */
    public function testEveryFactoryMethodReturnsAnEndpoint(): void
    {
        $client = new SofascoreClient(new MockTransport());
        $reflection = new ReflectionClass($client);

        $factories = 0;
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic() || $method->isConstructor()) {
                continue;
            }
            $returnType = $method->getReturnType();
            if (!$returnType instanceof ReflectionNamedType
                || !is_a($returnType->getName(), AbstractEndpoint::class, true)) {
                continue;
            }

            $args = array_map(
                static fn (ReflectionParameter $p): int|string => self::sampleArgument($p),
                $method->getParameters(),
            );

            $endpoint = $method->invokeArgs($client, $args);
            self::assertInstanceOf(AbstractEndpoint::class, $endpoint, $method->getName().'() must return an endpoint');
            ++$factories;
        }

        self::assertGreaterThanOrEqual(20, $factories, 'Expected a factory method per endpoint group');
    }

    public function testCreateBuildsAStandaloneClientOnTheHttpTransport(): void
    {
        self::assertInstanceOf(HttpClientTransport::class, SofascoreClient::create()->transport());
    }

    public function testTransportAccessor(): void
    {
        $transport = new MockTransport();
        self::assertSame($transport, (new SofascoreClient($transport))->transport());
    }

    private static function sampleArgument(ReflectionParameter $parameter): int|string
    {
        $type = $parameter->getType();
        $name = $type instanceof ReflectionNamedType ? $type->getName() : 'string';

        return 'int' === $name ? 1 : 'sample';
    }
}
