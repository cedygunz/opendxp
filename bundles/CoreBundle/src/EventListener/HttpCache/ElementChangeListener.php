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
use OpenDxp\Event\Model\TranslationEvent;
use OpenDxp\Event\TranslationEvents;
use OpenDxp\HttpCache\HttpCacheArguments;
use OpenDxp\HttpCache\HttpCache;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
class ElementChangeListener implements EventSubscriberInterface
{
    public function __construct(private readonly HttpCache $httpCache)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DocumentEvents::POST_ADD    => 'onDocumentChange',
            DocumentEvents::POST_UPDATE => 'onDocumentChange',
            DocumentEvents::POST_DELETE => 'onDocumentChange',

            DataObjectEvents::POST_ADD    => 'onDataObjectChange',
            DataObjectEvents::POST_UPDATE => 'onDataObjectChange',
            DataObjectEvents::POST_DELETE => 'onDataObjectChange',

            AssetEvents::POST_ADD    => 'onAssetChange',
            AssetEvents::POST_UPDATE => 'onAssetChange',
            AssetEvents::POST_DELETE => 'onAssetChange',

            TranslationEvents::POST_SAVE   => 'onTranslationChange',
            TranslationEvents::POST_DELETE => 'onTranslationChange',
        ];
    }

    public function onDocumentChange(DocumentEvent $event): void
    {
        if ($this->shouldSkip($event)) {
            return;
        }

        $this->httpCache->invalidate($event->getElement());
    }

    public function onDataObjectChange(DataObjectEvent $event): void
    {
        if ($this->shouldSkip($event)) {
            return;
        }

        $this->httpCache->invalidate($event->getElement());
    }

    public function onAssetChange(AssetEvent $event): void
    {
        if ($this->shouldSkip($event)) {
            return;
        }

        $this->httpCache->invalidate($event->getAsset());
    }

    public function onTranslationChange(TranslationEvent $event): void
    {
        if ($event->hasArgument(HttpCacheArguments::SKIP_INVALIDATION)) {
            return;
        }

        $this->httpCache->invalidate($event->getTranslation());
    }

    private function shouldSkip(DocumentEvent|DataObjectEvent|AssetEvent $event): bool
    {
        return $event->hasArgument('saveVersionOnly')
            || $event->hasArgument('autoSave')
            || $event->hasArgument(HttpCacheArguments::SKIP_INVALIDATION);
    }
}
