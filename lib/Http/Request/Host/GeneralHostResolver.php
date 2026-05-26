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

namespace OpenDxp\Http\Request\Host;

use OpenDxp\SystemSettingsConfig;

final class GeneralHostResolver
{
    /**
     * @param iterable<GeneralHostProviderInterface> $providers
     */
    public function __construct(private readonly iterable $providers = [])
    {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function resolve(array $context = []): ?string
    {
        foreach ($this->providers as $provider) {
            $domain = $provider->provide($context);
            if ($domain !== null && $domain !== '') {
                return $domain;
            }
        }

        return $this->getFallBackHost();
    }

    private function getFallBackHost(): ?string
    {

        $systemConfig = SystemSettingsConfig::get()['general'];

        return $systemConfig['domain'] ?? null;

    }
}
