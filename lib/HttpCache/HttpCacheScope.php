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

namespace OpenDxp\HttpCache;

use Symfony\Contracts\Service\ResetInterface;

/**
 * Controls whether HTTP cache tags are currently being collected.
 *
 * Two independent flags:
 *   $active: managed by HttpCacheScopeListener (infrastructure)
 *   $disabled: managed by the developer (application opt-out)
 */
class HttpCacheScope implements ResetInterface
{
    private bool $active = false;
    private bool $disabled = false;
    private bool $suspended = false;

    /**
     * Activates tag collection.
     * Has no effect if disable() was already called.
     */
    public function enable(): void
    {
        $this->active = true;
    }

    /**
     * Permanently disables tag collection for the current request.
     * Survives enable() calls.
     */
    public function disable(): void
    {
        $this->disabled = true;
    }

    public function isActive(): bool
    {
        return $this->active && !$this->disabled && !$this->suspended;
    }

    /**
     * Executes the callable with tag collection temporarily suspended.
     */
    public function suspended(callable $fn): mixed
    {
        $this->suspended = true;
        try {
            return $fn();
        } finally {
            $this->suspended = false;
        }
    }

    /**
     * Executes the callable with tag collection force-enabled,
     * regardless of the current scope state.
     */
    public function collecting(callable $fn): mixed
    {
        $previousActive = $this->active;
        $previousDisabled = $this->disabled;
        $previousSuspended = $this->suspended;

        $this->active = true;
        $this->disabled = false;
        $this->suspended = false;

        try {
            return $fn();
        } finally {
            $this->active = $previousActive;
            $this->disabled = $previousDisabled;
            $this->suspended = $previousSuspended;
        }
    }

    public function reset(): void
    {
        $this->active = false;
        $this->disabled = false;
        $this->suspended = false;
    }
}