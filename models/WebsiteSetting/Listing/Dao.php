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

namespace OpenDxp\Model\WebsiteSetting\Listing;

use OpenDxp\Model;

/**
 * @internal
 *
 * @property \OpenDxp\Model\WebsiteSetting\Listing $model
 */
class Dao extends Model\Listing\Dao\AbstractDao
{
    /**
     * @return \OpenDxp\Model\WebsiteSetting[]
     */
    public function load(): array
    {
        $sql = 'SELECT id FROM website_settings' . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit();
        $settingsData = $this->db->fetchFirstColumn($sql, $this->model->getConditionVariables(), $this->model->getConditionVariableTypes());

        $settings = [];
        foreach ($settingsData as $settingData) {
            $settings[] = Model\WebsiteSetting::getById($settingData);
        }

        $this->model->setSettings($settings);

        return $settings;
    }

    public function getTotalCount(): int
    {
        return (int) $this->db->fetchOne(
            'SELECT COUNT(*) as amount FROM website_settings ' . $this->getCondition(),
            $this->model->getConditionVariables(),
            $this->model->getConditionVariableTypes()
        );
    }
}
