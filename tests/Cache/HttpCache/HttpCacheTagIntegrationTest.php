<?php
declare(strict_types=1);

namespace OpenDxp\Tests\Cache\HttpCache;

use OpenDxp;
use OpenDxp\HttpCache\HttpCacheTagCollectorInterface;
use OpenDxp\Model\Document;
use OpenDxp\Tests\Support\Test\ModelTestCase;
use OpenDxp\Tests\Support\Util\TestHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class HttpCacheTagIntegrationTest extends ModelTestCase
{
    private HttpKernelInterface $kernel;
    private HttpCacheTagCollectorInterface $collector;

    protected function setUp(): void
    {
        parent::setUp();
        $container = OpenDxp::getContainer();
        $this->kernel = $container->get('http_kernel');
        $this->collector = $container->get(HttpCacheTagCollectorInterface::class);
    }

    protected function setUpTestClasses(): void
    {
        $this->tester->setupOpenDxpClass_Unittest();
    }

    public function testDocumentRouteTagInResponseHeader(): void
    {
        $doc = TestHelper::createEmptyDocumentPage();

        $response = $this->kernel->handle(Request::create($doc->getFullPath()));

        $this->assertTag('document_' . $doc->getId(), $response->headers->get('X-Cache-Tags', ''));
    }

    public function testDocumentPostLoadTagInResponseHeader(): void
    {
        $page = TestHelper::createEmptyDocumentPage();
        $loaded = TestHelper::createEmptyDocumentPage();

        $request = Request::create($page->getFullPath());
        $request->attributes->set('_template', 'test/tag_collection.html.twig');
        $request->attributes->set('test_doc_id', $loaded->getId());

        $response = $this->kernel->handle($request);
        $tags = $response->headers->get('X-Cache-Tags', '');

        $this->assertTag('document_' . $page->getId(), $tags);
        $this->assertTag('document_' . $loaded->getId(), $tags);
    }

    public function testAssetPostLoadTagInResponseHeader(): void
    {
        $page = TestHelper::createEmptyDocumentPage();
        $asset = TestHelper::createImageAsset();

        $request = Request::create($page->getFullPath());
        $request->attributes->set('_template', 'test/tag_collection.html.twig');
        $request->attributes->set('test_asset_id', $asset->getId());

        $response = $this->kernel->handle($request);
        $tags = $response->headers->get('X-Cache-Tags', '');

        $this->assertTag('document_' . $page->getId(), $tags);
        $this->assertTag('asset_' . $asset->getId(), $tags);
    }

    public function testDataObjectPostLoadTagInResponseHeader(): void
    {
        $page = TestHelper::createEmptyDocumentPage();
        $folder = TestHelper::createObjectFolder();

        $request = Request::create($page->getFullPath());
        $request->attributes->set('_template', 'test/tag_collection.html.twig');
        $request->attributes->set('test_obj_id', $folder->getId());

        $response = $this->kernel->handle($request);
        $tags = $response->headers->get('X-Cache-Tags', '');

        $this->assertTag('data_object_' . $folder->getId(), $tags);
    }

    public function testMultipleElementsAllTagged(): void
    {
        $page = TestHelper::createEmptyDocumentPage();
        $doc = TestHelper::createEmptyDocumentPage();
        $asset = TestHelper::createImageAsset();

        $request = Request::create($page->getFullPath());
        $request->attributes->set('_template', 'test/tag_collection.html.twig');
        $request->attributes->set('test_doc_id', $doc->getId());
        $request->attributes->set('test_asset_id', $asset->getId());

        $response = $this->kernel->handle($request);
        $tags = $response->headers->get('X-Cache-Tags', '');

        $this->assertTag('document_' . $doc->getId(), $tags);
        $this->assertTag('asset_' . $asset->getId(), $tags);
    }

    public function testDocumentListingAddsDocListTag(): void
    {
        $page = TestHelper::createEmptyDocumentPage();

        $request = Request::create($page->getFullPath());
        $request->attributes->set('test_doc_listing', true);

        $response = $this->kernel->handle($request);

        $this->assertTag('document_list', $response->headers->get('X-Cache-Tags', ''));
    }

    public function testAssetListingAddsAssetListTag(): void
    {
        $page = TestHelper::createEmptyDocumentPage();

        $request = Request::create($page->getFullPath());
        $request->attributes->set('test_asset_listing', true);

        $response = $this->kernel->handle($request);

        $this->assertTag('asset_list', $response->headers->get('X-Cache-Tags', ''));
    }

    public function testSubRequestTagsAccumulateInMainResponse(): void
    {
        $page = TestHelper::createEmptyDocumentPage();
        $mainDoc = TestHelper::createEmptyDocumentPage();
        $subDoc = TestHelper::createEmptyDocumentPage();

        $request = Request::create($page->getFullPath());

        $request->attributes->set('_template', 'test/tag_collection_subrequest.html.twig');
        $request->attributes->set('test_doc_id', $mainDoc->getId());
        $request->attributes->set('test_sub_doc_id', $subDoc->getId());

        $response = $this->kernel->handle($request);
        $tags = $response->headers->get('X-Cache-Tags', '');

        $this->assertTag('document_' . $mainDoc->getId(), $tags);
        $this->assertTag('document_' . $subDoc->getId(), $tags);
    }

    public function testTagsNotCollectedOutsideRequestScope(): void
    {
        $doc = TestHelper::createEmptyDocumentPage();

        Document::getById($doc->getId(), ['force' => true]);

        $this->assertTrue($this->collector->isEmpty());
    }

    public function testTagsResetBetweenRequests(): void
    {
        $doc1 = TestHelper::createEmptyDocumentPage();
        $doc2 = TestHelper::createEmptyDocumentPage();

        $this->kernel->handle(Request::create($doc1->getFullPath()));

        $response = $this->kernel->handle(Request::create($doc2->getFullPath()));
        $tags = $response->headers->get('X-Cache-Tags', '');

        $this->assertTag('document_' . $doc2->getId(), $tags);
        $this->assertStringNotContainsString('document_' . $doc1->getId(), $tags);
    }

    private function assertTag(string $tag, string $headerValue): void
    {
        $this->assertStringContainsString($tag, $headerValue, "Expected tag '$tag' in X-Cache-Tags: '$headerValue'");
    }
}