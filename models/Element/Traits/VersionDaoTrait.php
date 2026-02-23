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

namespace OpenDxp\Model\Element\Traits;

use OpenDxp\Model\Element;
use OpenDxp\Model\Version;

/**
 * @internal
 */
trait VersionDaoTrait
{
    /**
     * Get latest available version, using $includingPublished to also consider the published one
     */
    public function getLatestVersion(?int $userId = null, bool $includingPublished = false): ?Version
    {
        $operator = $includingPublished ? '>=' : '>';
        $versionId = $this->db->fetchOne(
            sprintf(
                'SELECT id FROM versions WHERE cid = :cid AND ctype = :ctype AND (`date` %1$s :mdate OR versionCount %1$s :versionCount) AND ((autoSave = 1 AND userId = :userId) OR autoSave = 0) ORDER BY `versionCount` DESC LIMIT 1',
                $operator
            ),
            [
                'cid' => $this->model->getId(),
                'ctype' => Element\Service::getElementType($this->model),
                'userId' => $userId,
                'mdate' => $this->model->getModificationDate(),
                'versionCount' => $this->model->getVersionCount(),
            ]
        );

        if ($versionId) {
            return Version::getById($versionId);
        }

        return null;
    }

    /**
     * Get available versions fot the object and return an array of them
     *
     * @return Version[]
     */
    public function getVersions(): array
    {
        $list = new Version\Listing();
        $list->setCondition('cid = :cid AND ctype = :ctype', [
            'cid' => $this->model->getId(),
            'ctype' => Element\Service::getElementType($this->model),
        ])->setOrderKey('id')->setOrder('ASC');

        return $list->load();
    }
}
