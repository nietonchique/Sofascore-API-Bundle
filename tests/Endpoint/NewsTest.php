<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use Nietonchique\SofascoreApiBundle\Endpoint\News;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(News::class)]
final class NewsTest extends TestCase
{
    private MockTransport $transport;

    private News $endpoint;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
        $this->endpoint = new News($this->transport, new Enums());
    }

    public function testNewsFeed(): void
    {
        $this->transport->setResponse(['newsArticles' => []]);
        $result = $this->endpoint->newsFeed();

        self::assertSame('/media/news-articles/sport/football', $this->transport->lastEndpoint());
        self::assertSame(['newsArticles' => []], $result);
    }
}
