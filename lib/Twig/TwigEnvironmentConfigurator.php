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

namespace OpenDxp\Twig;

use OpenDxp\Model\Document\Editable;
use Twig\Environment;
use Twig\Runtime\EscaperRuntime;

/**
 * @internal
 */
final readonly class TwigEnvironmentConfigurator
{
    /**
     * The inner configurator is typed as object because the decorator chain
     * may contain a third-party configurator (e.g. symfony/ux-twig-component)
     * that does not implement a common interface.
     *
     * @see https://github.com/symfony/symfony/issues/63808
     */
    public function __construct(private object $inner)
    {
    }

    public function configure(Environment $environment): void
    {
        if (method_exists($this->inner, 'configure')) {
            $this->inner->configure($environment);
        }

        $environment->getRuntime(EscaperRuntime::class)->addSafeClass(Editable::class, ['html']);
    }
}
