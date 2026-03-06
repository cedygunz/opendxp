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

namespace OpenDxp\Model\Site;

use OpenDxp\Model;
use OpenDxp\Model\Exception\NotFoundException;

/**
 * @internal
 *
 * @property \OpenDxp\Model\Site $model
 */
class Dao extends Model\Dao\AbstractDao
{
    /**
     * @throws NotFoundException
     */
    public function getById(int $id): void
    {
        $data = $this->db->fetchAssociative('SELECT * FROM sites WHERE id = ?', [$id]);

        if (!$data) {
            throw new NotFoundException(sprintf('Unable to load site with ID `%s`', $id));
        }

        $this->assignVariablesToModel($data);
    }

    /**
     * @throws NotFoundException
     */
    public function getByRootId(int $id): void
    {
        $data = $this->db->fetchAssociative('SELECT * FROM sites WHERE rootId = ?', [$id]);

        if (!$data) {
            throw new NotFoundException(sprintf('Unable to load site with ID `%s`', $id));
        }

        $this->assignVariablesToModel($data);
    }

    /**
     * @throws NotFoundException
     */
    public function getByDomain(string $domain): void
    {
        // 1. exact match
        $data = $this->db->fetchAssociative(
            'SELECT * FROM sites WHERE mainDomain = ? OR domains LIKE ?',
            [$domain, '%"' . $domain . '"%']
        );

        if ($data !== false) {
            $this->assignVariablesToModel($data);
            return;
        }

        // 2. wildcard match
        $sites = $this->db->fetchAllAssociative(
            'SELECT * FROM sites WHERE domains LIKE ?',
            ['%*%']
        );

        foreach ($sites as $site) {
            $domains = \OpenDxp\Tool\Serialize::unserialize($site['domains']);

            if (!is_array($domains)) {
                continue;
            }

            foreach ($domains as $siteDomain) {
                if (!$this->isWildcardDomain($siteDomain)) {
                    continue;
                }

                if ($this->matchesWildcardDomain($siteDomain, $domain)) {
                    $this->assignVariablesToModel($site);
                    return;
                }
            }
        }

        throw new NotFoundException(
            sprintf('there is no site for the requested domain: `%s`', $domain)
        );
    }

    public function save(): void
    {
        if (!$this->model->getId()) {
            $this->create();
        }

        $this->update();
    }

    public function create(): void
    {
        $ts = time();
        $this->model->setCreationDate($ts);
        $this->model->setModificationDate($ts);
        $this->db->insert('sites', ['rootId' => $this->model->getRootId()]);
        $this->model->setId((int) $this->db->lastInsertId());
    }

    public function update(): void
    {
        $ts = time();
        $this->model->setModificationDate($ts);

        $data = [];
        $site = $this->model->getObjectVars();

        foreach ($site as $key => $value) {
            if (in_array($key, $this->getValidTableColumns('sites'))) {
                if (is_array($value) || is_object($value)) {
                    $value = \OpenDxp\Tool\Serialize::serialize($value);
                }
                if (is_bool($value)) {
                    $value = (int) $value;
                }
                $data[$key] = $value;
            }
        }

        $this->db->update('sites', $data, ['id' => $this->model->getId()]);

        $this->model->clearDependentCache();
    }

    public function delete(): void
    {
        $this->db->delete('sites', ['id' => $this->model->getId()]);
        //clean slug table
        Model\DataObject\Data\UrlSlug::handleSiteDeleted($this->model->getId());

        $this->model->clearDependentCache();
    }

    private function isWildcardDomain(string $domain): bool
    {
        return str_contains($domain, '*');
    }

    private function matchesWildcardDomain(string $pattern, string $domain): bool
    {
        // backward compatibility
        $pattern = str_replace('.*', '*', $pattern);

        $regex = preg_quote($pattern, '#');
        $regex = str_replace('\*', '.*', $regex);

        return (bool) preg_match('#^' . $regex . '$#', $domain);
    }
}
