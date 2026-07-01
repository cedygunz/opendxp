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

use OpenDxp\Event\Model\WebsiteSettingLoadEvent;
use OpenDxp\Event\WebsiteSettingEvents;
use OpenDxp\HttpCache\HttpCache;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
class WebsiteSettingLoadListener implements EventSubscriberInterface
{
    public function __construct(private readonly HttpCache $httpCache)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WebsiteSettingEvents::POST_LOAD => 'onLoad',
            WebsiteSettingEvents::LIST_LOAD => 'onLoad',
            WebsiteSettingEvents::DATA_LOAD => 'onLoad',
        ];
    }

    public function onLoad(WebsiteSettingLoadEvent $event): void
    {
        $this->httpCache->collectTagsFor($event);
    }
}
