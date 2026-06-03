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

namespace OpenDxp\HttpCache;

use FOS\HttpCacheBundle\Http\SymfonyResponseTagger;
use OpenDxp\HttpCache\Tag\CacheTag;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Bridges OpenDXP's tag collection interface to FOSHttpCacheBundle's SymfonyResponseTagger.
 */
class HttpCacheTagCollector implements HttpCacheTagCollectorInterface, ResetInterface
{
    public function __construct(private readonly SymfonyResponseTagger $responseTagger)
    {
    }

    public function addTag(CacheTag ...$tags): void
    {
        $this->responseTagger->addTags(array_map(strval(...), $tags));
    }

    public function isEmpty(): bool
    {
        return !$this->responseTagger->hasTags();
    }

    public function reset(): void
    {
        $this->responseTagger->clear();
    }
}
