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

use OpenDxp\HttpCache\HttpCacheScope;
use OpenDxp\HttpCache\HttpCacheSettings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 */
class HttpCacheResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly HttpCacheScope $scope,
        private readonly int $sharedMaxAge,
        private readonly int $maxAge,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 20],
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->scope->isActive()) {
            return;
        }

        /** @var HttpCacheSettings|null $settings */
        $settings = $event->getRequest()->attributes->get('_http_cache_settings');

        $sharedMaxAge = $settings?->sharedMaxAge ?? $this->sharedMaxAge;
        $maxAge = $settings?->maxAge ?? $this->maxAge;

        $response = $event->getResponse();

        if ($sharedMaxAge === 0 || !$response->isSuccessful()) {
            return;
        }

        $response->setPublic();
        $response->setSharedMaxAge($sharedMaxAge);
        $response->setMaxAge($maxAge);
    }
}