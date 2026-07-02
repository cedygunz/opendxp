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

namespace OpenDxp\Bundle\CoreBundle\HttpCache\Strategy;

use OpenDxp\Event\Model\WebsiteSettingLoadEvent;
use OpenDxp\HttpCache\HttpCacheTagStrategyInterface;
use OpenDxp\HttpCache\Tag\CacheTag;
use OpenDxp\HttpCache\Tag\ElementTagType;
use OpenDxp\Model\WebsiteSetting;

/**
 * @internal
 */
class WebsiteSettingCacheStrategy implements HttpCacheTagStrategyInterface
{
    public function supports(object $element): bool
    {
        return $element instanceof WebsiteSetting || $element instanceof WebsiteSettingLoadEvent;
    }

    public function getTags(object $element): array
    {
        if ($element instanceof WebsiteSettingLoadEvent) {
            return match ($element->getType()) {
                WebsiteSettingLoadEvent::TYPE_SINGLE => $element->getSetting() !== null
                    ? [new CacheTag(ElementTagType::WebsiteSetting, $element->getSetting()->getId())]
                    : [],
                WebsiteSettingLoadEvent::TYPE_DATA => $element->getId() !== null
                    ? [new CacheTag(ElementTagType::WebsiteSetting, $element->getId())]
                    : [],
                WebsiteSettingLoadEvent::TYPE_LIST => [new CacheTag(ElementTagType::WebsiteSettingList)],
                default => throw new \LogicException(sprintf('Unsupported WebsiteSettingLoadEvent type: "%s"', $element->getType())),
            };
        }

        if (!$element instanceof WebsiteSetting) {
            return [];
        }

        return [
            new CacheTag(ElementTagType::WebsiteSetting, $element->getId()),
            new CacheTag(ElementTagType::WebsiteSettingList),
        ];
    }
}
