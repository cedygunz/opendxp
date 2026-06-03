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

use OpenDxp\Model\DataObject\Concrete;
use OpenDxp\Model\DataObject\Data\UrlSlug;
use OpenDxp\Model\Site;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Route;

/**
 * @internal
 */
final class DataObjectRoute extends Route implements RouteObjectInterface, HttpCacheTaggableInterface
{
    protected ?Concrete $object = null;

    protected ?UrlSlug $slug = null;

    protected ?Site $site = null;

    public function getObject(): ?Concrete
    {
        return $this->object;
    }

    /**
     * @return $this
     */
    public function setObject(Concrete $object): static
    {
        $this->object = $object;

        return $this;
    }

    public function getSlug(): ?UrlSlug
    {
        return $this->slug;
    }

    /**
     * @return $this
     */
    public function setSlug(UrlSlug $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    /**
     * @return $this
     */
    public function setSite(?Site $site): static
    {
        $this->site = $site;

        return $this;
    }

    public function getContent(): ?object
    {
        return null;
    }

    public function getCacheElement(): ?object
    {
        return $this->object;
    }

    public function getRouteKey(): ?string
    {
        if ($this->object) {
            return sprintf('data_object_%d_%d_%s', $this->object->getId(), $this->site?->getId(), $this->getPath());
        }

        return null;
    }
}
