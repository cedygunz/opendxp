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

namespace OpenDxp\Model\DataObject\Traits;

use Doctrine\DBAL\Connection;

/**
 * @internal
 *
 * @property Connection $db
 */
trait CompositeIndexTrait
{
    /**
     * @internal
     */
    public function updateCompositeIndices(string $table, string $type, array $compositeIndices): void
    {
        // fetch existing indices
        $existingMap = [];
        // prefix with "c_"
        $existingIndicesRaw = $this->db->fetchAllAssociative(
            sprintf('SHOW INDEXES FROM %s WHERE Key_Name LIKE "c\\_%%"',
                $this->db->quoteIdentifier($table)
            )
        );

        foreach ($existingIndicesRaw as $item) {
            $key = $item['Key_name'];
            $column = $item['Column_name'];
            if (!array_key_exists($key, $existingMap)) {
                $existingMap[$key] = [];
            }
            $existingMap[$key][] = $column;
        }

        foreach ($existingMap as $key => $columns) {
            $existingMap[$key] = implode(',', $columns);
        }

        $newIndicesFilteredByType = array_filter($compositeIndices, static fn ($item) =>
            // query or localized_query
            $item['index_type'] === $type
        );

        // key => plain comma-separated columns (for comparison with $existingMap)
        $newIndicesMap = [];
        // key => raw column array (for safe SQL generation)
        $newIndicesColumns = [];

        foreach ($newIndicesFilteredByType as $newIndex) {

            $key = 'c_' . $newIndex['index_key'];
            $columns = $newIndex['index_columns'];

            if (empty($columns)) {
                continue;
            }

            $newIndicesMap[$key] = implode(',', $columns);
            $newIndicesColumns[$key] = $columns;
        }

        $drop = [];
        $add = [];
        foreach ($existingMap as $key => $existing) {
            if (!isset($newIndicesMap[$key]) || $existing !== $newIndicesMap[$key]) {
                $drop[] = $key;
            }
        }

        foreach ($newIndicesMap as $key => $new) {
            if (!isset($existingMap[$key]) || $existingMap[$key] !== $new) {
                $add[] = $key;
            }
        }

        foreach ($drop as $key) {
            $this->db->executeQuery(sprintf(
                'ALTER TABLE %s DROP INDEX %s;',
                $this->db->quoteIdentifier($table),
                $this->db->quoteIdentifier($key)
            ));
        }

        foreach ($add as $key) {

            $quotedColumns = implode(
                ', ',
                array_map($this->db->quoteIdentifier(...), $newIndicesColumns[$key])
            );

            $this->db->executeQuery(sprintf(
                'ALTER TABLE %s ADD INDEX %s (%s);',
                $this->db->quoteIdentifier($table),
                $this->db->quoteIdentifier($key),
                $quotedColumns
            ));
        }
    }
}
