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

use OpenDxp\Event\AssetEvents;
use OpenDxp\Event\DataObjectEvents;
use OpenDxp\Event\DocumentEvents;
use OpenDxp\Event\Model\AssetEvent;
use OpenDxp\Event\Model\DataObjectEvent;
use OpenDxp\Event\Model\DocumentEvent;
use OpenDxp\HttpCache\HttpCache;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
class HttpCachePostLoadListener implements EventSubscriberInterface
{
    public function __construct(private readonly HttpCache $httpCache)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DocumentEvents::POST_LOAD   => 'onDocumentPostLoad',
            DataObjectEvents::POST_LOAD => 'onDataObjectPostLoad',
            AssetEvents::POST_LOAD      => 'onAssetPostLoad',
        ];
    }

    public function onDocumentPostLoad(DocumentEvent $event): void
    {
        $this->httpCache->collectTagsFor($event->getElement());
    }

    public function onDataObjectPostLoad(DataObjectEvent $event): void
    {
        $this->httpCache->collectTagsFor($event->getElement());
    }

    public function onAssetPostLoad(AssetEvent $event): void
    {
        $this->httpCache->collectTagsFor($event->getElement());
    }
}
