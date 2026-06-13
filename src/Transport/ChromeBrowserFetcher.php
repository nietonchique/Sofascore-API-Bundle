<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Transport;

use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Exception\CommunicationException;
use HeadlessChromium\Exception\NavigationExpired;
use HeadlessChromium\Exception\NoResponseAvailable;
use HeadlessChromium\Exception\OperationTimedOut;
use HeadlessChromium\Page;
use RuntimeException;

/**
 * Production {@see BrowserFetcherInterface} backed by chrome-php/chrome.
 *
 * Drives a headless Chromium via the DevTools protocol (no Node.js required),
 * navigates to the API URL and returns the document body — which, for an API
 * endpoint, is the raw JSON.
 *
 * Before hitting the API it first loads a warm-up page (the SofaScore site root)
 * so Chrome can pass Cloudflare's challenge and obtain a clearance cookie in the
 * shared session. A fresh browser is spawned per request because this path is
 * only ever used as a Cloudflare fallback.
 *
 * Note: a real residential IP and/or a non-headless profile is usually required
 * to clear SofaScore's Cloudflare protection; datacenter IPs are often blocked
 * regardless of the browser. See the README "Cloudflare & 403" section.
 *
 * @see https://github.com/chrome-php/chrome
 */
final class ChromeBrowserFetcher implements BrowserFetcherInterface
{
    private const DEFAULT_USER_AGENT = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';

    public function __construct(
        private readonly string $binary = 'google-chrome-stable',
        private readonly bool $headless = true,
        private readonly int $timeoutMs = 30_000,
        private readonly ?string $warmupUrl = 'https://www.sofascore.com/',
        private readonly string $userAgent = self::DEFAULT_USER_AGENT,
        private readonly ?string $proxy = null,
    ) {
    }

    /**
     * Drives a real headless browser, so it is exercised by the live integration
     * tests (group "network") rather than unit tests.
     *
     * @codeCoverageIgnore
     */
    public function fetch(string $url): string
    {
        $customFlags = ['--disable-blink-features=AutomationControlled', '--lang=en-US'];
        if (null !== $this->proxy) {
            $customFlags[] = '--proxy-server='.$this->proxy;
        }

        $browser = (new BrowserFactory($this->binary))->createBrowser([
            'headless' => $this->headless,
            'noSandbox' => true,
            'windowSize' => [1366, 768],
            'userAgent' => $this->userAgent,
            'customFlags' => $customFlags,
        ]);

        try {
            $page = $browser->createPage();

            if (null !== $this->warmupUrl) {
                try {
                    $page->navigate($this->warmupUrl)->waitForNavigation(Page::LOAD, $this->timeoutMs);
                } catch (OperationTimedOut) {
                    // Warm-up is best-effort; proceed to the real request regardless.
                }
            }

            $page->navigate($url)->waitForNavigation(Page::LOAD, $this->timeoutMs);

            $value = $page->evaluate('document.body ? document.body.innerText : ""')->getReturnValue();

            return \is_string($value) ? $value : '';
        } catch (OperationTimedOut|NavigationExpired|NoResponseAvailable|CommunicationException $e) {
            throw new RuntimeException(\sprintf('Chrome navigation to "%s" failed: %s', $url, $e->getMessage()), 0, $e);
        } finally {
            $browser->close();
        }
    }
}
