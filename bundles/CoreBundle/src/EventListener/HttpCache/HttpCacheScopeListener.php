<?php
declare(strict_types=1);

/**
 * OpenDXP
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\CoreBundle\EventListener\HttpCache;

use OpenDxp\Http\Request\Resolver\DocumentResolver;
use OpenDxp\Http\Request\Resolver\OpenDxpContextResolver;
use OpenDxp\HttpCache\HttpCache;
use OpenDxp\HttpCache\HttpCacheScope;
use OpenDxp\Model\Document;
use OpenDxp\Routing\HttpCacheTaggableInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 */
class HttpCacheScopeListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly HttpCache $httpCache,
        private readonly HttpCacheScope $httpCacheScope,
        private readonly OpenDxpContextResolver $contextResolver,
        private readonly DocumentResolver $documentResolver,
        private readonly bool $collectFromRequest = false,
        private readonly bool $tagFallbackDocument = true,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST    => ['onKernelRequest', 512],
            KernelEvents::CONTROLLER => ['onKernelController', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$this->collectFromRequest || !$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (
            !$request->isMethodCacheable() ||
            $this->contextResolver->matchesOpenDxpContext($request, OpenDxpContextResolver::CONTEXT_ADMIN)
        ) {
            return;
        }

        $this->httpCacheScope->enable();
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->collectFromRequest) {

            if (
                !$request->isMethodCacheable() ||
                $this->contextResolver->matchesOpenDxpContext($request, OpenDxpContextResolver::CONTEXT_ADMIN)
            ) {
                return;
            }

            $this->httpCacheScope->enable();
        }

        $route = $request->attributes->get(DynamicRouter::ROUTE_KEY);
        $content = $this->documentResolver->getDocument($request);

        $routeElement = $route instanceof HttpCacheTaggableInterface ? $route->getCacheElement() : null;

        if ($routeElement !== null) {
            $this->httpCache->collectTagsFor($routeElement);
        }

        // if the route itself didn't resolve to a Document, any $content here can only be a fallback document
        // (e.g. nearest document by path for a custom route), not something the route matched
        $isFallback = !$routeElement instanceof Document && $content instanceof Document;

        if ($isFallback && $this->tagFallbackDocument) {
            $this->httpCache->collectTagsFor($content);
        }
    }
}
