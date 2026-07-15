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

namespace OpenDxp\HttpCache\Tag;

final class CacheTag implements \Stringable
{
    public function __construct(
        public readonly CacheTagType $type,
        public readonly string|int|null $identifier = null,
    ) {
    }

    public function __toString(): string
    {
        $prefix = $this->type->prefix();

        return $this->identifier !== null
            ? $prefix . '_' . strtolower((string) $this->identifier)
            : $prefix;
    }
}
