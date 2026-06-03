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

use OpenDxp\HttpCache\HttpCacheTagStrategyInterface;
use OpenDxp\HttpCache\Tag\CacheTag;
use OpenDxp\HttpCache\Tag\ElementTagType;
use OpenDxp\Model\Asset;
use OpenDxp\Model\Asset\Listing as AssetListing;
use OpenDxp\Model\DataObject;
use OpenDxp\Model\DataObject\Concrete;
use OpenDxp\Model\DataObject\Listing\Concrete as ObjectListing;
use OpenDxp\Model\Document;
use OpenDxp\Model\Document\Listing as DocumentListing;

/**
 * @internal
 */
class OpenDxpElementCacheStrategy implements HttpCacheTagStrategyInterface
{
    public function __construct(
        private readonly bool $documentsEnabled,
        private readonly bool $documentsTagList,
        private readonly bool $dataObjectsEnabled,
        private readonly bool $dataObjectsTagList,
        private readonly bool $assetsEnabled,
        private readonly bool $assetsTagList,
    ) {
    }

    public function supports(object $element): bool
    {
        return match (true) {
            $element instanceof Document, $element instanceof DocumentListing => $this->documentsEnabled,
            $element instanceof DataObject, $element instanceof ObjectListing => $this->dataObjectsEnabled,
            $element instanceof Asset, $element instanceof AssetListing => $this->assetsEnabled,
            default => false,
        };
    }

    public function getTags(object $element): array
    {
        return match (true) {
            # listings
            $element instanceof AssetListing => $this->tag(ElementTagType::AssetList, $this->assetsTagList),
            $element instanceof DocumentListing => $this->tag(ElementTagType::DocumentList, $this->documentsTagList),
            $element instanceof ObjectListing => $this->tag(ElementTagType::DataObjectClass, $this->dataObjectsTagList, $element->getClassName()),
            # single elements
            $element instanceof Asset => [
                ...$this->tag(ElementTagType::Asset, true, $element->getId()),
                ...$this->tag(ElementTagType::AssetList, $this->assetsTagList)
            ],
            $element instanceof Document => [
                ...$this->tag(ElementTagType::Document, true, $element->getId()),
                ...$this->tag(ElementTagType::DocumentList, $this->documentsTagList)
            ],
            $element instanceof DataObject => [
                ...$this->tag(ElementTagType::DataObject, true, $element->getId()),
                ...($element instanceof Concrete ? $this->tag(ElementTagType::DataObjectClass, $this->dataObjectsTagList, $element->getClassName()) : []),
            ],
            default => [],
        };
    }

    /** @return CacheTag[] */
    private function tag(ElementTagType $type, bool $enabled = true, string|int|null $qualifier = null): array
    {
        return $enabled ? [new CacheTag($type, $qualifier)] : [];
    }
}
