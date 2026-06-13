<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use Nietonchique\SofascoreApiBundle\Endpoint\Manager;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Manager::class)]
final class ManagerTest extends TestCase
{
    private MockTransport $transport;

    private Manager $endpoint;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
        $this->endpoint = new Manager($this->transport, new Enums(), 794);
    }

    public function testGetManager(): void
    {
        $this->transport->setResponse(['manager' => ['name' => 'Mikel Arteta', 'id' => 794]]);
        $result = $this->endpoint->getManager();

        self::assertSame('/manager/794', $this->transport->lastEndpoint());
        self::assertSame(['manager' => ['name' => 'Mikel Arteta', 'id' => 794]], $result);
    }
}
