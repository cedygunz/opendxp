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

namespace OpenDxp\Event\HttpCache;

use OpenDxp\HttpCache\Tag\CacheTag;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Guard event fired before cache tags enter the response collector.
 *
 * @see HttpCacheEvents::TAG_GUARD
 */
class HttpCacheTagGuardEvent extends Event
{
    private bool $cancelled = false;

    /** @param CacheTag[] $tags */
    public function __construct(
        private readonly array $tags,
        public readonly object $element,
    ) {
    }

    /** @return CacheTag[] */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function cancel(): void
    {
        $this->cancelled = true;
    }

    public function isCancelled(): bool
    {
        return $this->cancelled;
    }
}
