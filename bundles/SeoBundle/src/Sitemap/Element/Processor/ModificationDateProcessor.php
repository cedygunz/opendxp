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
 * @copyright  Copyright (c) Pimcore GmbH (https://pimcore.com)
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\SeoBundle\Sitemap\Element\Processor;

use DateTime;
use OpenDxp\Bundle\SeoBundle\Sitemap\Element\GeneratorContextInterface;
use OpenDxp\Bundle\SeoBundle\Sitemap\Element\ProcessorInterface;
use OpenDxp\DateFormat;
use OpenDxp\Model\Element\ElementInterface;
use Presta\SitemapBundle\Sitemap\Url\Url;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;

/**
 * Adds modification date from element modification date.
 */
class ModificationDateProcessor implements ProcessorInterface
{
    public function process(Url $url, ElementInterface $element, GeneratorContextInterface $context): Url|UrlConcrete|null
    {
        if (!$url instanceof UrlConcrete) {
            return $url;
        }

        $url->setLastmod(DateTime::createFromFormat(DateFormat::UNIX_TIMESTAMP, (string)$element->getModificationDate()));

        return $url;
    }
}
