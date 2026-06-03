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

use OpenDxp\HttpCache\Tag\CacheTag;
use Symfony\Contracts\Service\ResetInterface;

class TraceableHttpCacheTagCollector implements HttpCacheTagCollectorInterface, ResetInterface
{
    /**
     * @var CacheTag[]
     */
    private array $collectedTags = [];

    public function __construct(private readonly HttpCacheTagCollectorInterface $inner)
    {
    }

    public function addTag(CacheTag ...$tags): void
    {
        array_push($this->collectedTags, ...$tags);
        $this->inner->addTag(...$tags);
    }

    public function isEmpty(): bool
    {
        return $this->inner->isEmpty();
    }

    public function reset(): void
    {
        $this->collectedTags = [];
        $this->inner->reset();
    }

    /**
     * @return CacheTag[]
     */
    public function getCollectedTags(): array
    {
        return $this->collectedTags;
    }
}
