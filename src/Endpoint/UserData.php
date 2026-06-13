<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

/**
 * UserData endpoint group. Methods are a faithful port of the corresponding Python
 * `sofascore_wrapper` module.
 */
final class UserData extends AbstractEndpoint
{
    /**
     * The current sofascore news RSS feed (Python {@code sofascore_news_rss_feed}).
     *
     * @return array<string, mixed>
     */
    public function sofascoreNewsRssFeed(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->getRaw('https://www.sofascore.com/news/category/app/iphone/feed/');

        return $data;
    }

    /**
     * The given user data (Python {@code user_account}).
     *
     * @return array<string, mixed>
     */
    public function userAccount(string $userId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/user-account/{$userId}");

        return $data;
    }

    /**
     * The given user flares data (Python {@code user_flares}).
     *
     * @return array<string, mixed>
     */
    public function userFlares(string $userId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/flare/user/{$userId}");

        return $data;
    }

    /**
     * The given user's last predictions (Python {@code last_user_predictions}).
     *
     * @return array<string, mixed>
     */
    public function lastUserPredictions(string $userId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/user-account/{$userId}/predictions/last/0");

        return $data;
    }

    /**
     * The given user's next predictions (Python {@code next_user_predictions}).
     *
     * @return array<string, mixed>
     */
    public function nextUserPredictions(string $userId): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get("/user-account/{$userId}/predictions/next/0");

        return $data;
    }

    /**
     * The top 100 contribution leaderboard (Python {@code contribution_leaderboard}).
     *
     * @return array<string, mixed>
     */
    public function contributionLeaderboard(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/user-account/contribution-ranking-score');

        return $data;
    }

    /**
     * The top 100 predictions leaderboard (Python {@code predictions_leaderboard}).
     *
     * @return array<string, mixed>
     */
    public function predictionsLeaderboard(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/user-account/vote-ranking');

        return $data;
    }

    /**
     * The top 100 editors leaderboard (Python {@code editor_leaderboard}).
     *
     * @return array<string, mixed>
     */
    public function editorLeaderboard(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/user-account/editor-ranking');

        return $data;
    }
}
