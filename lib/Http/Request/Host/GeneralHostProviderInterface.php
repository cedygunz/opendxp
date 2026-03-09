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

namespace OpenDxp\Http\Request\Host;

/**
 * Implement this interface and tag the service with `opendxp.general_host_provider`
 * to participate in general host resolution.
 *
 * Providers are tried in descending priority order. The first non-null return value wins.
 * If no provider returns a value, the static `opendxp.general.domain` config is used as fallback.
 *
 * The optional $context array may carry arbitrary caller-supplied information, e.g.:
 *   ['source' => $consoleInput]
 *   ['source' => $request]
 *   ['source' => $site]
 */
interface GeneralHostProviderInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function provide(array $context = []): ?string;
}