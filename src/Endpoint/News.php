<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Endpoint;

/**
 * News endpoint group. Methods are a faithful port of the corresponding Python
 * `sofascore_wrapper` module.
 */
final class News extends AbstractEndpoint
{
    /**
     * Sofascore's latest football published news articles (Python {@code news_feed}).
     *
     * @return array<string, mixed>
     */
    public function newsFeed(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->get('/media/news-articles/sport/football');

        return $data;
    }
}
