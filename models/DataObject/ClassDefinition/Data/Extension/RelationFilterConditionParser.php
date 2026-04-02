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

namespace OpenDxp\Model\DataObject\ClassDefinition\Data\Extension;

use OpenDxp\Db\Helper;

/**
 * Trait RelationFilterConditionParser
 *
 * @package OpenDxp\Model\DataObject\ClassDefinition\Data\Extension
 */
trait RelationFilterConditionParser
{
    /**
     * Parses filter value of a relation field and creates the filter condition
     */
    public function getRelationFilterCondition(?string $value, string $operator, string $name, string $brickPrefix = ''): string
    {
        $db = \OpenDxp\Db::get();
        $key = $brickPrefix . $db->quoteIdentifier($name);
        $result = $key . ' IS NULL';
        if ($value === null || $value === 'null') {
            return $result;
        }
        if ($operator === '=') {
            return $key . ' = ' . $db->quote($value);
        }
        $values = explode(',', $value);
        $fieldConditions = array_map(function ($value) use ($key, $db) {
            $quotedValue = $db->quote('%,' . Helper::escapeLike($value) . ',%');

            return $key . ' LIKE ' . $quotedValue . ' ';
        }, array_filter($values));
        if ($fieldConditions !== []) {
            return '(' . implode(' AND ', $fieldConditions) . ')';
        }

        return $result;
    }
}
