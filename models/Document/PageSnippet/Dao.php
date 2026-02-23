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

namespace OpenDxp\Model\Document\PageSnippet;

use OpenDxp;
use OpenDxp\Model;
use OpenDxp\Model\Document;

/**
 * @internal
 *
 * @property \OpenDxp\Model\Document\PageSnippet $model
 */
abstract class Dao extends Model\Document\Dao
{
    use Model\Element\Traits\VersionDaoTrait;

    /**
     * Delete all editables containing the content from the database
     */
    public function deleteAllEditables(): void
    {
        $this->db->delete('documents_editables', ['documentId' => $this->model->getId()]);
    }

    /**
     * Get all editables containing the content from the database
     *
     * @return Document\Editable[]
     */
    public function getEditables(): array
    {
        $editablesRaw = $this->db->fetchAllAssociative(
            'SELECT * FROM documents_editables WHERE documentId = ?',
            [$this->model->getId()]
        );

        $editables = [];
        $loader = OpenDxp::getContainer()->get(Document\Editable\Loader\EditableLoader::class);

        foreach ($editablesRaw as $editableRaw) {
            /** @var Document\Editable $editable */
            $editable = $loader->build($editableRaw['type']);
            $editable->setName($editableRaw['name']);
            $editable->setDocument($this->model);
            $editable->setDataFromResource($editableRaw['data']);

            $editables[$editableRaw['name']] = $editable;
        }

        return $editables;
    }
}
