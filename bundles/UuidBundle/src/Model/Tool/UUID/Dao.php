<?php

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

namespace OpenDxp\Bundle\UuidBundle\Model\Tool\UUID;

use Doctrine\DBAL\Types\Types;
use Exception;
use OpenDxp\Bundle\UuidBundle\Model\Tool\UUID;
use OpenDxp\Db\Helper;
use OpenDxp\Model;

/**
 * @internal
 *
 * @property UUID $model
 */
class Dao extends Model\Dao\AbstractDao
{
    public const string TABLE_NAME = 'uuids';

    public function save(): void
    {
        $data = $this->getValidObjectVars();

        Helper::upsert($this->db, self::TABLE_NAME, $data, $this->getPrimaryKey(self::TABLE_NAME));
    }

    public function create(): void
    {
        $data = $this->getValidObjectVars();

        $this->db->insert(self::TABLE_NAME, $data);
    }

    private function getValidObjectVars(): array
    {
        $data = $this->model->getObjectVars();

        foreach ($data as $key => $value) {
            if (!in_array($key, $this->getValidTableColumns(static::TABLE_NAME))) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    public function delete(): void
    {
        $uuid = $this->model->getUuid();
        if (!$uuid) {
            throw new Exception("Couldn't delete UUID - no UUID specified.");
        }

        $itemId = $this->model->getItemId();
        $type = $this->model->getType();

        $this->db->delete(self::TABLE_NAME, ['itemId' => $itemId, 'type' => $type, 'uuid' => $uuid]);
    }

    public function getByUuid(string $uuid): UUID
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->where('uuid = :uuid')
            ->setParameter('uuid', $uuid, Types::STRING);

        $data = $queryBuilder
            ->executeQuery()
            ->fetchAssociative();

        $model = new UUID();
        $model->setValues($data);

        return $model;
    }

    public function exists(string $uuid): bool
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder
            ->select('uuid')
            ->from(self::TABLE_NAME)
            ->where('uuid = :uuid')
            ->setParameter('uuid', $uuid, Types::STRING);

        $result = $queryBuilder
            ->executeQuery()
            ->fetchOne();

        return (bool) $result;
    }
}
