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

namespace OpenDxp\Bundle\CoreBundle\Doctrine;

/**
 * Implement this interface on a console command to apply the same schema filter
 * that doctrine:schema:update and doctrine:schema:validate use automatically:
 *
 * Non-ORM tables are invisible to schema comparisons, preventing unintended
 * DROP TABLE statements for tables Doctrine does not manage.
 */
interface ExcludesUnmanagedTablesInterface
{
}
