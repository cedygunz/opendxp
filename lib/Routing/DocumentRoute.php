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

namespace OpenDxp\Routing;

use OpenDxp\Model\Document;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Route;

/**
 * @internal
 */
final class DocumentRoute extends Route implements RouteObjectInterface, HttpCacheTaggableInterface
{
    protected ?Document $document = null;

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(Document $document): static
    {
        $this->document = $document;

        return $this;
    }

    public function getContent(): ?object
    {
        return $this->getDocument();
    }

    public function getCacheElement(): ?object
    {
        return $this->document;
    }

    public function getRouteKey(): ?string
    {
        if ($this->document) {
            return sprintf('document_%d', $this->document->getId());
        }

        return null;
    }
}
