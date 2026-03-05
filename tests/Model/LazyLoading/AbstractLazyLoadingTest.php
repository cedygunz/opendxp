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

namespace OpenDxp\Tests\Model\LazyLoading;

use Exception;
use OpenDxp\Cache;
use OpenDxp\Model\DataObject\AbstractObject;
use OpenDxp\Model\DataObject\Concrete;
use OpenDxp\Model\DataObject\LazyLoading;
use OpenDxp\Model\DataObject\RelationTest;
use OpenDxp\Model\DataObject\Service;
use OpenDxp\Tests\Support\Test\ModelTestCase;
use OpenDxp\Tests\Support\Util\TestHelper;

class AbstractLazyLoadingTest extends ModelTestCase
{
    public const int RELATION_COUNT = 5;

    public function setUp(): void
    {
        parent::setUp();
        TestHelper::cleanUp();

        $this->createRelationObjects();
    }

    public function tearDown(): void
    {
        TestHelper::cleanUp();
        parent::tearDown();
    }

    protected function setUpTestClasses(): void
    {
        $this->tester->setupOpenDxpClass_RelationTest();
        $this->tester->setupFieldcollection_LazyLoadingTest();

        $this->tester->setupFieldcollection_LazyLoadingLocalizedTest();
        $this->tester->setupOpenDxpClass_LazyLoading();

        $this->tester->setupObjectbrick_LazyLoadingTest();

        $this->tester->setupObjectbrick_LazyLoadingLocalizedTest();
    }

    protected function createRelationObjects(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $object = new RelationTest();
            $object->setParent(Service::createFolderByPath('__test/relationobjects'));
            $object->setKey("relation-$i");
            $object->setPublished(true);
            $object->setSomeAttribute("Some content $i");
            $object->save();
        }
    }

    protected function createDataObject(): LazyLoading
    {
        $object = new LazyLoading();
        $object->setParentId(1);
        $object->setKey('lazy1');
        $object->setPublished(true);

        return $object;
    }

    /**
     * @throws Exception
     */
    protected function createChildDataObject(AbstractObject $parent): LazyLoading
    {
        $object = new LazyLoading();
        $object->setParent($parent);
        $object->setKey('sub-lazy');
        $object->setPublished(true);
        $object->save();

        return $object;
    }

    /**
     * @throws Exception
     */
    protected function loadRelations(): RelationTest\Listing
    {
        $listing = new RelationTest\Listing();
        $listing->setLimit(self::RELATION_COUNT);

        return $listing;
    }

    protected function loadSingleRelation(): RelationTest
    {
        $listing = new RelationTest\Listing();
        $listing->setLimit(1);

        return $listing->load()[0];
    }

    protected function checkSerialization(LazyLoading $object, string $messagePrefix, bool $contentShouldBeIncluded = false): void
    {
        $serializedString = serialize($object);
        $this->checkSerializedStringForNeedle($serializedString, ['lazyLoadedFields', 'lazyKeys', 'loadedLazyKeys'], false, $messagePrefix);
        $this->checkSerializedStringForNeedle($serializedString, 'someAttribute";s:14:"Some content', $contentShouldBeIncluded, $messagePrefix);
    }

    /**
     * @param string[]|string $needle
     */
    protected function checkSerializedStringForNeedle(string $string, array|string $needle, bool $expected, string $messagePrefix = null): void
    {
        if (!is_array($needle)) {
            $needle = [$needle];
        }

        foreach ($needle as $item) {
            $this->assertEquals($expected, str_contains($string, $item), $messagePrefix . "Check if '$item' is occuring in serialized data.");
        }
    }

    protected function forceSavingAndLoadingFromCache(Concrete $object, callable $callback): void
    {
        //enable cache
        $cacheEnabled = Cache::isEnabled();
        if (!$cacheEnabled) {
            Cache::enable();
            Cache::getHandler()->setHandleCli(true);
        }

        //save object to cache
        Cache::getHandler()->removeClearedTags($object->getCacheTags());
        Cache::save($object, \OpenDxp\Model\Element\Service::getElementCacheTag('object', $object->getId()), [], null, 9999, true);

        Cache\RuntimeCache::clear();
        //reload from cache and check again
        $objectCache = Concrete::getById($object->getId());

        //once more reload object from database to check consistency of
        //data object loaded from cache
        Concrete::getById($object->getId(), ['force' => true]);

        $callback($objectCache);

        if (!$cacheEnabled) {
            Cache::disable();
            Cache::getHandler()->setHandleCli(false);
        }
    }
}
