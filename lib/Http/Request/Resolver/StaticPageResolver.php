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

namespace OpenDxp\Http\Request\Resolver;

use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
class StaticPageResolver extends AbstractRequestResolver
{
    public const string ATTRIBUTE_OPENDXP_STATIC_PAGE = '_opendxp_static_page';

    public function hasStaticPageContext(Request $request): bool
    {
        return $request->attributes->has(self::ATTRIBUTE_OPENDXP_STATIC_PAGE);
    }

    public function setStaticPageContext(Request $request): void
    {
        $request->attributes->set(self::ATTRIBUTE_OPENDXP_STATIC_PAGE, true);
    }
}
