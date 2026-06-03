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

use OpenDxp\Http\Request\Resolver\OpenDxpContextResolver;
use OpenDxp\HttpCache\HttpCache;
use OpenDxp\HttpCache\HttpCacheScope;
use OpenDxp\Routing\HttpCacheTaggableInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
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
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 0],
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->isMethodCacheable()
            || $this->contextResolver->matchesOpenDxpContext($request, OpenDxpContextResolver::CONTEXT_ADMIN)) {
            return;
        }

        $this->httpCacheScope->enable();

        $route = $request->attributes->get(DynamicRouter::ROUTE_KEY);
        if ($route instanceof HttpCacheTaggableInterface) {
            $element = $route->getCacheElement();
            if ($element !== null) {
                $this->httpCache->collectTagsFor($element);
            }
        }

        $content = $request->attributes->get(DynamicRouter::CONTENT_KEY);
        if ($content !== null) {
            $this->httpCache->collectTagsFor($content);
        }
    }
}