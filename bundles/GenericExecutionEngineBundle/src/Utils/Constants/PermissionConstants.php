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

namespace OpenDxp\Bundle\GenericExecutionEngineBundle\Utils\Constants;

/**
 * @internal
 */
class PermissionConstants
{
    /**
     * Permission have a max length of 50 chars!
     */
    public const string GEE_JOB_RUN = 'gee_job_run_permission';
    public const string GEE_SEE_ALL_JOB_RUNS = 'gee_see_all_job_runs_permission';
}
