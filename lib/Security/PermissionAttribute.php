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

namespace OpenDxp\Security;

final class PermissionAttribute
{
    public const string PREFIX = 'opendxp:security:permission:';

    public const string ELEMENT_PREFIX = 'opendxp:security:element:permission:';

    public static function for(string $attribute): string
    {
        return str_replace([self::PREFIX, self::ELEMENT_PREFIX], '', $attribute);
    }
}
