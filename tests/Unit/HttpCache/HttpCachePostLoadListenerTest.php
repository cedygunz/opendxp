<?php
declare(strict_types=1);

namespace OpenDxp\Tests\Unit\HttpCache;

use OpenDxp\Bundle\CoreBundle\EventListener\HttpCache\HttpCachePostLoadListener;
use OpenDxp\Event\Model\AssetEvent;
use OpenDxp\Event\Model\DataObjectEvent;
use OpenDxp\Event\Model\DocumentEvent;
use OpenDxp\HttpCache\HttpCache;
use OpenDxp\Model\Asset;
use OpenDxp\Model\DataObject;
use OpenDxp\Model\Document;
use OpenDxp\Tests\Support\Test\TestCase;

class HttpCachePostLoadListenerTest extends TestCase
{
    private HttpCache $invalidator;
    private HttpCachePostLoadListener $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invalidator = $this->createMock(HttpCache::class);
        $this->listener = new HttpCachePostLoadListener($this->invalidator);
    }

    public function testDocumentPostLoadDelegatesToInvalidator(): void
    {
        $document = $this->createMock(Document::class);

        $event = $this->createMock(DocumentEvent::class);
        $event->method('getElement')->willReturn($document);

        $this->invalidator->expects($this->once())->method('collectTagsFor')->with($document);

        $this->listener->onDocumentPostLoad($event);
    }

    public function testDataObjectPostLoadDelegatesToInvalidator(): void
    {
        $object = $this->createMock(DataObject::class);

        $event = $this->createMock(DataObjectEvent::class);
        $event->method('getElement')->willReturn($object);

        $this->invalidator->expects($this->once())->method('collectTagsFor')->with($object);

        $this->listener->onDataObjectPostLoad($event);
    }

    public function testAssetPostLoadDelegatesToInvalidator(): void
    {
        $asset = $this->createMock(Asset::class);

        $event = $this->createMock(AssetEvent::class);
        $event->method('getElement')->willReturn($asset);

        $this->invalidator->expects($this->once())->method('collectTagsFor')->with($asset);

        $this->listener->onAssetPostLoad($event);
    }
}