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

use OpenDxp\Bundle\SeoBundle\Sitemap\Element\GeneratorContextInterface;
use OpenDxp\Bundle\SeoBundle\Sitemap\Element\ProcessorInterface;
use OpenDxp\Model\Element\ElementInterface;
use Presta\SitemapBundle\Sitemap\Url\Url;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;

/**
 * Adds change frequency and priority entries based on document properties.
 */
class PropertiesProcessor implements ProcessorInterface
{
    public const string PROPERTY_CHANGE_FREQUENCY = 'sitemaps_changefreq';

    public const string PROPERTY_PRIORITY = 'sitemaps_priority';

    public function process(Url $url, ElementInterface $element, GeneratorContextInterface $context): Url|UrlConcrete|null
    {
        if (!$url instanceof UrlConcrete) {
            return $url;
        }

        $changeFreq = $element->getProperty(self::PROPERTY_CHANGE_FREQUENCY);
        if (!empty($changeFreq)) {
            $url->setChangefreq($changeFreq);
        }

        $priority = $element->getProperty(self::PROPERTY_PRIORITY);
        if (!empty($priority)) {
            $url->setPriority($priority);
        }

        return $url;
    }
}
