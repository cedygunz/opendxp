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

use OpenDxp\Model\Document\PageSnippet;
use OpenDxp\Twig\Extension\Templating\Inc;
use Twig\Attribute\AsTwigFunction;

/**
 * @internal
 */
class SubrequestExtension
{
    public function __construct(protected Inc $incHelper)
    {
    }

    #[AsTwigFunction('opendxp_inc', isSafe: ['html'])]
    public function inc(int|string|PageSnippet $include, array $params = [], bool $cacheEnabled = true, ?bool $editmode = null): string
    {
        return ($this->incHelper)($include, $params, $cacheEnabled, $editmode);
    }
}