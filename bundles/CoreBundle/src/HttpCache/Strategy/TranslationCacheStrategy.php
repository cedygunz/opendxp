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
use OpenDxp\Model\Translation;
use OpenDxp\Model;

/**
 * @internal
 */
class TranslationCacheStrategy implements HttpCacheTagStrategyInterface
{
    public function supports(object $element): bool
    {
        return $element instanceof Translation && $element->getDomain() !== Model\Translation::DOMAIN_ADMIN;
    }

    public function getTags(object $element): array
    {
        return [new CacheTag(ElementTagType::Translation)];
    }
}
