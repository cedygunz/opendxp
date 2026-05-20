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

use OpenDxp\Navigation\Container;
use OpenDxp\Navigation\Renderer\RendererInterface;
use OpenDxp\Twig\Extension\Templating\Navigation;
use Twig\Attribute\AsTwigFunction;

/**
 * @internal
 */
class NavigationExtension
{
    public function __construct(private readonly Navigation $navigationExtension)
    {
    }

    #[AsTwigFunction('opendxp_build_nav')]
    public function buildNav(array $params): Container
    {
        return $this->navigationExtension->build($params);
    }

    #[AsTwigFunction('opendxp_render_nav', isSafe: ['html'])]
    public function renderNav(Container $container, string $rendererName = 'menu', string $renderMethod = 'render', mixed ...$rendererArguments): string
    {
        return $this->navigationExtension->render($container, $rendererName, $renderMethod, ...$rendererArguments);
    }

    #[AsTwigFunction('opendxp_nav_renderer')]
    public function getNavRenderer(string $alias): RendererInterface
    {
        return $this->navigationExtension->getRenderer($alias);
    }
}
