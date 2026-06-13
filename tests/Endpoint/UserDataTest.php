<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\Endpoint;

use Nietonchique\SofascoreApiBundle\Endpoint\UserData;
use Nietonchique\SofascoreApiBundle\Enum\Enums;
use Nietonchique\SofascoreApiBundle\Tests\Support\MockTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserData::class)]
final class UserDataTest extends TestCase
{
    private MockTransport $transport;

    private UserData $endpoint;

    protected function setUp(): void
    {
        $this->transport = new MockTransport();
        $this->endpoint = new UserData($this->transport, new Enums());
    }

    public function testSofascoreNewsRssFeed(): void
    {
        $this->endpoint->sofascoreNewsRssFeed();

        self::assertSame('https://www.sofascore.com/news/category/app/iphone/feed/', $this->transport->lastEndpoint());
    }

    public function testUserAccount(): void
    {
        $this->endpoint->userAccount('abc123');

        self::assertSame('/user-account/abc123', $this->transport->lastEndpoint());
    }

    public function testUserFlares(): void
    {
        $this->endpoint->userFlares('abc123');

        self::assertSame('/flare/user/abc123', $this->transport->lastEndpoint());
    }

    public function testLastUserPredictions(): void
    {
        $this->endpoint->lastUserPredictions('abc123');

        self::assertSame('/user-account/abc123/predictions/last/0', $this->transport->lastEndpoint());
    }

    public function testNextUserPredictions(): void
    {
        $this->endpoint->nextUserPredictions('abc123');

        self::assertSame('/user-account/abc123/predictions/next/0', $this->transport->lastEndpoint());
    }

    public function testContributionLeaderboard(): void
    {
        $this->endpoint->contributionLeaderboard();

        self::assertSame('/user-account/contribution-ranking-score', $this->transport->lastEndpoint());
    }

    public function testPredictionsLeaderboard(): void
    {
        $this->endpoint->predictionsLeaderboard();

        self::assertSame('/user-account/vote-ranking', $this->transport->lastEndpoint());
    }

    public function testEditorLeaderboard(): void
    {
        $this->endpoint->editorLeaderboard();

        self::assertSame('/user-account/editor-ranking', $this->transport->lastEndpoint());
    }
}
