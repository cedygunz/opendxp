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

namespace OpenDxp\Extension\Document\Areabrick;

/**
 * Bricks implementing this interface auto-resolve view templates if hasTemplate() properties are set.
 * Depending on the result of getTemplateLocation() and getTemplateSuffix() the tag handler builds the
 * following references:
 *
 * - @<bundle>/areas/<brickId>/view.<suffix>
 *      -> resolves to <bundle>/templates/areas/<brickId>/view.<suffix> (Symfony >= 5 structure)
 *         or <bundle>/Resources/views/areas/<brickId>/view.<suffix> (Symfony <= 4 structure)
 * - areas/<brickId>/view.<suffix>
 *      -> resolves to <project>/templates/areas/<brickId>/view.<suffix>
 */
interface TemplateAreabrickInterface extends AreabrickInterface
{
    public const string TEMPLATE_LOCATION_GLOBAL = 'global';

    public const string TEMPLATE_LOCATION_BUNDLE = 'bundle';

    public const string TEMPLATE_SUFFIX_TWIG = 'html.twig';

    /**
     * Determines if template should be auto-located in bundle or in project
     */
    public function getTemplateLocation(): string;

    /**
     * Returns view suffix used to auto-build view names
     */
    public function getTemplateSuffix(): string;
}
