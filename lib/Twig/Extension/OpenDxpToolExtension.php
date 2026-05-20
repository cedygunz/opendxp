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

namespace OpenDxp\Twig\Extension;

use OpenDxp\Tool;
use OpenDxp\Tool\DeviceDetector;
use Twig\Attribute\AsTwigFunction;

/**
 * @internal
 */
class OpenDxpToolExtension
{
    #[AsTwigFunction('opendxp_supported_locales')]
    public function getSupportedLocales(): array
    {
        return Tool::getSupportedLocales();
    }

    #[AsTwigFunction('opendxp_device', isSafe: ['html'])]
    public function getDevice(?string $default = null): DeviceDetector
    {
        return DeviceDetector::getInstance($default);
    }
}