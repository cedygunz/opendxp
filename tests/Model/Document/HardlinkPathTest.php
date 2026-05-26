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
 * @copyright  Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Tests\Model\Document;

use OpenDxp;
use OpenDxp\Cache\RuntimeCache;
use OpenDxp\Http\RequestHelper;
use OpenDxp\Model\Document;
use OpenDxp\Model\Document\Hardlink\Service as HardlinkService;
use OpenDxp\Model\Document\Hardlink\Wrapper\WrapperInterface;
use OpenDxp\Model\Site;
use OpenDxp\Tests\Support\Test\ModelTestCase;
use OpenDxp\Tests\Support\Util\TestHelper;
use ReflectionProperty;
use Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class HardlinkPathTest extends ModelTestCase
{
    private ?Site $siteA = null;

    private ?Site $siteB = null;

    private ?Document\Page $siteARoot = null;

    private ?Document\Page $siteBRoot = null;

    private ?Document\Page $siteASection = null;

    private ?Document\Page $siteASubpage = null;

    private ?Document\Hardlink $hardlink = null;

    private RequestStack $requestStack;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestStack = OpenDxp::getContainer()->get('request_stack');
    }

    protected function tearDown(): void
    {
        while ($this->requestStack->getCurrentRequest() !== null) {
            $this->requestStack->pop();
        }

        $ref = new ReflectionProperty(Site::class, 'currentSite');
        $ref->setValue(null, null);

        $this->siteA?->delete();
        $this->siteB?->delete();

        RuntimeCache::getInstance()->offsetUnset('sites_path_mapping');

        parent::tearDown();
    }

    public function testSameSiteDocumentReturnsRelativePath(): void
    {
        $this->setUpSites();

        $page = new Document\Page();
        $page->setParentId($this->siteBRoot->getId());
        $page->setKey('b-page');
        $page->setPublished(true);
        $page->save();

        Site::setCurrentSite($this->siteB);
        $this->pushFrontendRequest('http://domain-b.test/b-page');

        $path = $page->getFullPath(true);

        $this->assertStringNotContainsString('://', $path, 'Same-site document must not produce an absolute URL');
        $this->assertSame('/b-page', $path);
    }

    public function testNoSiteRequestReturnsSimplePath(): void
    {
        // No Site::setCurrentSite() → isSiteRequest() = false
        $page = TestHelper::createEmptyDocumentPage('no-site-');

        $this->pushFrontendRequest('http://example.test/no-site-page');

        $path = $page->getFullPath(true);

        $this->assertStringNotContainsString('://', $path, 'Without a site request there must be no absolute URL');
        $this->assertSame($page->getPath() . $page->getKey(), $path);
    }

    public function testCrossSiteDocumentWithHardlinkContextReturnsHardlinkRelativePath(): void
    {
        $this->setUpSites();

        $wrappedHardlink = $this->getWrappedHardlink();

        Site::setCurrentSite($this->siteB);
        $this->pushFrontendRequest('http://domain-b.test/hl/', $wrappedHardlink);

        $path = $this->siteASubpage->getFullPath(true);

        $expectedHardlinkBase = str_replace(
            $this->siteBRoot->getRealFullPath(),
            '',
            $this->hardlink->getRealFullPath()
        );

        $this->assertSame(
            $expectedHardlinkBase . '/subpage',
            $path,
            'Cross-site document inside hardlink scope must be rewritten to the hardlink path'
        );

        $this->assertStringNotContainsString('://', $path, 'Result must be relative, not an absolute URL');
    }

    public function testCrossSiteRootDocumentReturnsAbsoluteUrlWithTrailingSlash(): void
    {
        $this->setUpSites();

        Site::setCurrentSite($this->siteB);
        $this->pushFrontendRequest('http://domain-b.test/');

        $path = $this->siteARoot->getFullPath(true);

        $this->assertSame('http://domain-a.test/', $path);
    }

    public function testCrossSiteDocumentWithoutHardlinkContextReturnsAbsoluteUrl(): void
    {
        $this->setUpSites();

        Site::setCurrentSite($this->siteB);
        // No WrapperInterface as CONTENT_KEY — plain page request on Site B
        $this->pushFrontendRequest('http://domain-b.test/b-page');

        $path = $this->siteASubpage->getFullPath(true);

        $this->assertStringStartsWith(
            'http://domain-a.test',
            $path,
            'Cross-site document without hardlink context must use the foreign site domain'
        );
        $this->assertStringContainsString('/section/subpage', $path);
    }

    public function testCrossSiteDocumentOutsideHardlinkSourceScopeFallsBackToAbsoluteUrl(): void
    {
        $this->setUpSites();

        // A page directly under Site A root — NOT under /section (the hardlink source)
        $otherPage = new Document\Page();
        $otherPage->setParentId($this->siteARoot->getId());
        $otherPage->setKey('other-page');
        $otherPage->setPublished(true);
        $otherPage->save();

        $wrappedHardlink = $this->getWrappedHardlink();

        Site::setCurrentSite($this->siteB);
        $this->pushFrontendRequest('http://domain-b.test/hl/', $wrappedHardlink);

        $path = $otherPage->getFullPath(true);

        $this->assertStringStartsWith(
            'http://domain-a.test',
            $path,
            'Document outside the hardlink source scope must produce an absolute URL to its own site'
        );
        $this->assertStringContainsString('/other-page', $path);
    }

    public function testNestedSnippetSubRequestStillUsesMainRequestHardlinkContext(): void
    {
        $this->setUpSites();

        $wrappedHardlink = $this->getWrappedHardlink();

        Site::setCurrentSite($this->siteB);

        // Level 0 — main request: the hardlink-wrapped page
        $this->pushFrontendRequest('http://domain-b.test/hl/', $wrappedHardlink);

        // Level 1 — sub-request: a snippet rendered inside the page.
        // The snippet is the real (unwrapped) document; CONTENT_KEY is a plain Snippet.
        $snippet = TestHelper::createEmptyDocument('snippet-', true, true, Document\Snippet::class);
        $subRequest = Request::create('http://domain-b.test/hl/');
        $subRequest->attributes->set(RequestHelper::ATTRIBUTE_FRONTEND_REQUEST, true);
        $subRequest->attributes->set(DynamicRouter::CONTENT_KEY, $snippet);
        $this->requestStack->push($subRequest);

        // Even though getCurrentRequest() has no WrapperInterface,
        // the path for the cross-site subpage must still be rewritten.
        $path = $this->siteASubpage->getFullPath(true);

        $expectedHardlinkBase = str_replace(
            $this->siteBRoot->getRealFullPath(),
            '',
            $this->hardlink->getRealFullPath()
        );

        $this->assertSame(
            $expectedHardlinkBase . '/subpage',
            $path,
            'Hardlink path rewrite must work even when a sub-request (snippet) is the current request'
        );

        $this->requestStack->pop(); // remove sub-request
    }

    public function testBrokenHardlinkSourceDocumentDoesNotCauseTypeError(): void
    {
        $this->setUpSites();

        $wrappedHardlink = $this->getWrappedHardlink();

        $crossSitePage = new Document\Page();
        $crossSitePage->setParentId($this->siteARoot->getId());
        $crossSitePage->setKey('survivor-page');
        $crossSitePage->setPublished(true);
        $crossSitePage->save();

        $this->siteASection->delete();
        $this->siteASection = null;
        $this->siteASubpage = null;

        Site::setCurrentSite($this->siteB);
        $this->pushFrontendRequest('http://domain-b.test/hl/', $wrappedHardlink);

        $path = $crossSitePage->getFullPath(true);

        $this->assertIsString($path, 'getFullPath() must return a string even with a broken hardlink source');
        $this->assertStringStartsWith('http://', $path, 'Fallback must be an absolute URL when hardlink rewrite is impossible');
    }

    public function testCurrentSiteRootDocumentReturnsSlash(): void
    {
        $this->setUpSites();

        Site::setCurrentSite($this->siteB);
        $this->pushFrontendRequest('http://domain-b.test/');

        $path = $this->siteBRoot->getFullPath(true);

        $this->assertSame('/', $path);
    }

    public function testAdminPreviewFromDifferentDomainProducesAbsoluteUrl(): void
    {
        $this->setUpSites();

        $request = Request::create('http://domain-b.test/some-path?opendxp_preview=1');
        $request->attributes->set(RequestHelper::ATTRIBUTE_FRONTEND_REQUEST, true);
        $this->requestStack->push($request);

        $path = $this->siteASubpage->getFullPath(true);

        $this->assertStringStartsWith('http://domain-a.test', $path);
        $this->assertStringContainsString('/section/subpage', $path);
    }

    public function testCrossSiteDocumentWithNoSiteAndNoHostResolverReturnsTreePath(): void
    {
        $this->setUpSites();

        $orphan = new Document\Page();
        $orphan->setParentId(1);
        $orphan->setKey('orphan-' . uniqid('', true));
        $orphan->setPublished(true);
        $orphan->save();

        Site::setCurrentSite($this->siteB);
        $this->pushFrontendRequest('http://domain-b.test/');

        $path = $orphan->getFullPath(true);

        // The code falls back to getPath() + getKey()
        $this->assertIsString($path);
        $this->assertStringNotContainsString('domain-b.test', $path, 'Orphan document must not be rewritten to Site B domain');
    }

    private function setUpSites(): void
    {
        RuntimeCache::getInstance()->offsetUnset('sites_path_mapping');

        $this->siteARoot = TestHelper::createEmptyDocumentPage('site-a-root-');

        $this->siteA = new Site();
        $this->siteA->setRootId($this->siteARoot->getId());
        $this->siteA->setMainDomain('domain-a.test');
        $this->siteA->save();

        $this->siteASection = new Document\Page();
        $this->siteASection->setParentId($this->siteARoot->getId());
        $this->siteASection->setKey('section');
        $this->siteASection->setPublished(true);
        $this->siteASection->save();

        $this->siteASubpage = new Document\Page();
        $this->siteASubpage->setParentId($this->siteASection->getId());
        $this->siteASubpage->setKey('subpage');
        $this->siteASubpage->setPublished(true);
        $this->siteASubpage->save();

        $this->siteBRoot = TestHelper::createEmptyDocumentPage('site-b-root-');

        $this->siteB = new Site();
        $this->siteB->setRootId($this->siteBRoot->getId());
        $this->siteB->setMainDomain('domain-b.test');
        $this->siteB->save();

        $this->hardlink = new Document\Hardlink();
        $this->hardlink->setParentId($this->siteBRoot->getId());
        $this->hardlink->setKey('hl');
        $this->hardlink->setSourceId($this->siteASection->getId());
        $this->hardlink->setChildrenFromSource(true);
        $this->hardlink->setPublished(true);
        $this->hardlink->save();

        RuntimeCache::getInstance()->offsetUnset('sites_path_mapping');
    }

    private function getWrappedHardlink(): WrapperInterface
    {
        $wrapped = HardlinkService::wrap($this->hardlink);
        $this->assertInstanceOf(WrapperInterface::class, $wrapped);

        return $wrapped;
    }

    private function pushFrontendRequest(string $url, mixed $contentDocument = null): Request
    {
        $request = Request::create($url);
        $request->attributes->set(RequestHelper::ATTRIBUTE_FRONTEND_REQUEST, true);

        if ($contentDocument !== null) {
            $request->attributes->set(DynamicRouter::CONTENT_KEY, $contentDocument);
        }

        $this->requestStack->push($request);

        return $request;
    }
}
