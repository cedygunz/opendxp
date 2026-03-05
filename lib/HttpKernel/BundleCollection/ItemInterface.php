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

namespace OpenDxp\HttpKernel\BundleCollection;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

interface ItemInterface
{
    public const string SOURCE_PROGRAMATICALLY = 'programatically';

    public const string SOURCE_EXTENSION_MANAGER_CONFIG = 'extension_manager_config';

    public function getBundleIdentifier(): string;

    public function getBundle(): BundleInterface;

    public function isOpenDxpBundle(): bool;

    public function getPriority(): int;

    /**
     * @return string[]
     */
    public function getEnvironments(): array;

    /**
     * Registers dependent bundles if the bundle implements DependentBundleInterface
     */
    public function registerDependencies(BundleCollection $collection): void;

    public function matchesEnvironment(string $environment): bool;

    public function getSource(): string;
}
