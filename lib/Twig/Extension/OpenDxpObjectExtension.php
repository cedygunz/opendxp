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

namespace OpenDxp\Twig\Extension;

use OpenDxp\Helper\StringHelper;
use OpenDxp\Model\Asset;
use OpenDxp\Model\DataObject;
use OpenDxp\Model\DataObject\Classificationstore\GroupConfig;
use OpenDxp\Model\DataObject\Objectbrick\Definition;
use OpenDxp\Model\Document;
use OpenDxp\Model\Document\Hardlink\Wrapper;
use OpenDxp\Model\Site;
use OpenDxp\Model\User;
use Twig\Attribute\AsTwigFunction;

/**
 * @internal
 */
class OpenDxpObjectExtension
{
    #[AsTwigFunction('opendxp_document')]
    public function getDocument(int $id, array $params = []): ?Document
    {
        return Document::getById($id, $params);
    }

    #[AsTwigFunction('opendxp_document_by_path')]
    public function getDocumentByPath(string $path, array $params = []): ?Document
    {
        return Document::getByPath($path, $params);
    }

    #[AsTwigFunction('opendxp_site')]
    public function getSite(int $id): ?Site
    {
        return Site::getById($id);
    }

    #[AsTwigFunction('opendxp_site_by_root_id')]
    public function getSiteByRootId(int $id): ?Site
    {
        return Site::getByRootId($id);
    }

    #[AsTwigFunction('opendxp_site_by_domain')]
    public function getSiteByDomain(string $domain): ?Site
    {
        return Site::getByDomain($domain);
    }

    #[AsTwigFunction('opendxp_site_is_request')]
    public function isSiteRequest(): bool
    {
        return Site::isSiteRequest();
    }

    #[AsTwigFunction('opendxp_site_current')]
    public function getCurrentSite(): Site
    {
        return Site::getCurrentSite();
    }

    #[AsTwigFunction('opendxp_asset')]
    public function getAsset(int $id, array $params = []): ?Asset
    {
        return Asset::getById($id, $params);
    }

    #[AsTwigFunction('opendxp_asset_by_path')]
    public function getAssetByPath(string $path, array $params = []): ?Asset
    {
        return Asset::getByPath($path, $params);
    }

    #[AsTwigFunction('opendxp_object')]
    public function getDataObject(int $id, array $params = []): ?DataObject\AbstractObject
    {
        return DataObject::getById($id, $params);
    }

    #[AsTwigFunction('opendxp_object_by_path')]
    public function getDataObjectByPath(string $path, array $params = []): ?DataObject\AbstractObject
    {
        return DataObject::getByPath($path, $params);
    }

    #[AsTwigFunction('opendxp_document_wrap_hardlink')]
    public function wrapHardlink(Document $doc): Wrapper\WrapperInterface|Wrapper\Hardlink|null
    {
        return Document\Hardlink\Service::wrap($doc);
    }

    #[AsTwigFunction('opendxp_user')]
    public function getUser(int $id): ?User
    {
        return User::getById($id);
    }

    #[AsTwigFunction('opendxp_object_classificationstore_group')]
    public function getClassificationstoreGroup(int $id, ?bool $force = false): ?GroupConfig
    {
        return GroupConfig::getById($id, $force);
    }

    #[AsTwigFunction('opendxp_object_classificationstore_get_field_definition_from_json')]
    public function getFieldDefinitionFromJson(array|string $definition, string $type): ?DataObject\ClassDefinition\Data
    {
        if (StringHelper::isValidJson($definition)) {
            $definition = json_decode($definition, true);
        }

        return DataObject\Classificationstore\Service::getFieldDefinitionFromJson($definition, $type);
    }

    #[AsTwigFunction('opendxp_object_brick_definition_key')]
    public function getObjectbrickDefinitionByKey(string $key): ?Definition
    {
        return Definition::getByKey($key);
    }
}