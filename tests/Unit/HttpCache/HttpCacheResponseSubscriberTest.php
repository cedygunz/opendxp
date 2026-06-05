<?php
declare(strict_types=1);

namespace OpenDxp\Tests\Unit\HttpCache;

use OpenDxp\Bundle\CoreBundle\EventListener\HttpCache\HttpCacheResponseSubscriber;
use OpenDxp\HttpCache\HttpCacheScope;
use OpenDxp\HttpCache\HttpCacheSettings;
use OpenDxp\Tests\Support\Test\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class HttpCacheResponseSubscriberTest extends TestCase
{
    private HttpCacheScope $scope;
    private HttpKernelInterface $kernel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scope = $this->createMock(HttpCacheScope::class);
        $this->kernel = $this->createMock(HttpKernelInterface::class);
    }

    public function testSetsPublicHeadersWhenScopeIsActive(): void
    {
        $this->scope->method('isActive')->willReturn(true);

        $response = $this->dispatch(sharedMaxAge: 3600, maxAge: 0);

        $this->assertTrue($response->headers->hasCacheControlDirective('public'));
        $this->assertSame(3600, $response->getMaxAge());
        $this->assertTrue($response->headers->hasCacheControlDirective('s-maxage'));
    }

    public function testSetsMaxAge(): void
    {
        $this->scope->method('isActive')->willReturn(true);

        $response = $this->dispatch(sharedMaxAge: 3600, maxAge: 120);

        $this->assertSame(120, (int) $response->headers->getCacheControlDirective('max-age'));
    }

    public function testDoesNothingWhenScopeIsInactive(): void
    {
        $this->scope->method('isActive')->willReturn(false);

        $response = $this->dispatch(sharedMaxAge: 3600, maxAge: 0);

        $this->assertFalse($response->headers->hasCacheControlDirective('public'));
    }

    public function testDoesNothingWhenSharedMaxAgeIsZero(): void
    {
        $this->scope->method('isActive')->willReturn(true);

        $response = $this->dispatch(sharedMaxAge: 0, maxAge: 0);

        $this->assertFalse($response->headers->hasCacheControlDirective('public'));
    }

    public function testDoesNothingForSubRequest(): void
    {
        $this->scope->method('isActive')->willReturn(true);

        $response = $this->dispatch(sharedMaxAge: 3600, maxAge: 0, isMain: false);

        $this->assertFalse($response->headers->hasCacheControlDirective('public'));
    }

    public function testReadsSettingsFromRequestAttribute(): void
    {
        $this->scope->method('isActive')->willReturn(true);

        $settings = new HttpCacheSettings(sharedMaxAge: 600, maxAge: 60);
        $response = $this->dispatch(sharedMaxAge: 3600, maxAge: 0, settings: $settings);

        $this->assertSame(600, (int) $response->headers->getCacheControlDirective('s-maxage'));
        $this->assertSame(60, (int) $response->headers->getCacheControlDirective('max-age'));
    }

    public function testRequestAttributeWithZeroSharedMaxAgeDisablesHeaders(): void
    {
        $this->scope->method('isActive')->willReturn(true);

        $settings = new HttpCacheSettings(sharedMaxAge: 0);
        $response = $this->dispatch(sharedMaxAge: 3600, maxAge: 0, settings: $settings);

        $this->assertFalse($response->headers->hasCacheControlDirective('public'));
    }

    private function dispatch(
        int $sharedMaxAge,
        int $maxAge,
        bool $isMain = true,
        ?HttpCacheSettings $settings = null,
    ): Response {

        $subscriber = new HttpCacheResponseSubscriber($this->scope, $sharedMaxAge, $maxAge);

        $request = Request::create('/');
        if ($settings !== null) {
            $request->attributes->set('_http_cache_settings', $settings);
        }

        $response = new Response();
        $event = new ResponseEvent(
            $this->kernel,
            $request,
            $isMain ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::SUB_REQUEST,
            $response,
        );

        $subscriber->onKernelResponse($event);

        return $response;
    }
}