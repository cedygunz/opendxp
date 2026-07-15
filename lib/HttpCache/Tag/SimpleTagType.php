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

/**
 * Simple CacheTagType backed by a plain string prefix.
 */
final class SimpleTagType implements CacheTagType
{
    public function __construct(private readonly string $prefix)
    {
    }

    public function prefix(): string
    {
        return $this->prefix;
    }
}