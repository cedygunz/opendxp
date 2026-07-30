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

namespace OpenDxp\Security;

enum ElementPermission: string
{
    case List        = 'opendxp:security:element:permission:list';
    case View        = 'opendxp:security:element:permission:view';
    case Save        = 'opendxp:security:element:permission:save';
    case Publish     = 'opendxp:security:element:permission:publish';
    case Unpublish   = 'opendxp:security:element:permission:unpublish';
    case Delete      = 'opendxp:security:element:permission:delete';
    case Rename      = 'opendxp:security:element:permission:rename';
    case Create      = 'opendxp:security:element:permission:create';
    case Settings    = 'opendxp:security:element:permission:settings';
    case Versions    = 'opendxp:security:element:permission:versions';
    case Properties  = 'opendxp:security:element:permission:properties';
}