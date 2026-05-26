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

namespace OpenDxp\Tests\Model\Site;

use InvalidArgumentException;
use OpenDxp\Model\Site;
use OpenDxp\Tests\Support\Test\ModelTestCase;
use OpenDxp\Tests\Support\Util\TestHelper;

/**
 * @group model.site
 */
class SiteTest extends ModelTestCase
{
    private function createSite(): Site
    {
        $rootPage = TestHelper::createEmptyDocumentPage('site-root-');

        $site = new Site();
        $site->setRootId($rootPage->getId());
        $site->setMainDomain('example.com');
        $site->save();

        return $site;
    }

    public function testCustomSettingsDefaultsToEmptyArray(): void
    {
        $site = new Site();

        $this->assertSame([], $site->getCustomSettings());
        $this->assertSame([], $site->getCustomSettings('anyScope'));
    }

    public function testCustomSettingsSetAndGetWithScope(): void
    {
        $site = new Site();
        $site->setCustomSettings(['myBundle' => ['color' => 'red', 'size' => 42]]);

        $this->assertSame(['color' => 'red', 'size' => 42], $site->getCustomSettings('myBundle'));
    }

    public function testCustomSettingsGetUnknownScopeReturnsEmptyArray(): void
    {
        $site = new Site();
        $site->setCustomSettings(['myBundle' => ['key' => 'val']]);

        $this->assertSame([], $site->getCustomSettings('unknownBundle'));
    }

    public function testCustomSettingsGetWithoutScopeReturnsAll(): void
    {
        $settings = ['bundleA' => ['x' => 1], 'bundleB' => ['y' => 2]];
        $site = new Site();
        $site->setCustomSettings($settings);

        $this->assertSame($settings, $site->getCustomSettings());
    }

    public function testCustomSettingsSetNullReturnsEmptyArrays(): void
    {
        $site = new Site();
        $site->setCustomSettings(['foo' => 'bar']);
        $site->setCustomSettings(null);

        $this->assertSame([], $site->getCustomSettings());
        $this->assertSame([], $site->getCustomSettings('foo'));
    }

    public function testCustomSettingsAcceptsSerializedString(): void
    {
        $site = new Site();
        $site->setCustomSettings(serialize(['bundleA' => ['active' => true]]));

        $this->assertSame(['active' => true], $site->getCustomSettings('bundleA'));
    }

    public function testCustomSettingsPersistedAndReloaded(): void
    {
        $site = $this->createSite();
        $site->setCustomSettings(['myBundle' => ['theme' => 'dark']]);
        $site->save();

        $reloaded = Site::getById($site->getId());

        $this->assertNotNull($reloaded);
        $this->assertSame(['theme' => 'dark'], $reloaded->getCustomSettings('myBundle'));
    }

    public function testCustomSettingsNullPersistedAndReloaded(): void
    {
        $site = $this->createSite();
        $site->setCustomSettings(null);
        $site->save();

        $reloaded = Site::getById($site->getId());

        $this->assertNotNull($reloaded);
        $this->assertSame([], $reloaded->getCustomSettings());
    }

    public function testGetByDomainExactMainDomain(): void
    {
        $site = $this->createSite();

        $found = Site::getByDomain('example.com');

        $this->assertNotNull($found);
        $this->assertSame($site->getId(), $found->getId());
    }

    public function testGetByDomainExactInDomainsList(): void
    {
        $site = $this->createSite();
        $site->setDomains(['alias.example.com', 'other.example.com']);
        $site->save();

        $found = Site::getByDomain('alias.example.com');

        $this->assertNotNull($found);
        $this->assertSame($site->getId(), $found->getId());
    }

    public function testGetByDomainWildcard(): void
    {
        $site = $this->createSite();
        $site->setDomains(['*.example.com']);
        $site->save();

        $found = Site::getByDomain('sub.example.com');

        $this->assertNotNull($found);
        $this->assertSame($site->getId(), $found->getId());
    }

    public function testGetByDomainWildcardDoesNotMatchParentDomain(): void
    {
        // Site with a different mainDomain so the exact-match path is not triggered
        $rootPage = TestHelper::createEmptyDocumentPage('site-wildcard-root-');

        $site = new Site();
        $site->setRootId($rootPage->getId());
        $site->setMainDomain('main.other.com');
        $site->setDomains(['*.example.com']);
        $site->save();

        $found = Site::getByDomain('example.com');

        $this->assertNull($found);
    }

    public function testGetByDomainUnknownReturnsNull(): void
    {
        $found = Site::getByDomain('does-not-exist-' . uniqid() . '.com');

        $this->assertNull($found);
    }

    public function testSetDomainsRejectsInvalidDomain(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $site = new Site();
        $site->setDomains(['not a valid domain!!']);
    }

    public function testSetDomainsAcceptsWildcardDomain(): void
    {
        $site = new Site();
        $site->setDomains(['*.example.com']);

        $this->assertSame(['*.example.com'], $site->getDomains());
    }
}
