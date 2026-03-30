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

namespace OpenDxp\Tests\Service\Element;

use OpenDxp\Db;
use OpenDxp\Model\Asset;
use OpenDxp\Model\Document\Page;
use OpenDxp\Model\User;
use OpenDxp\Model\Element\Service;
use OpenDxp\Tests\Support\Test\TestCase;
use OpenDxp\Tests\Support\Util\TestHelper;

class ServicePermittedPathsTest extends TestCase
{
    protected User $adminUser;
    protected User $regularUser;

    protected function needsDb(): bool
    {
        return true;
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->cleanAllWorkspaces();

        $this->adminUser = $this->createUser('test-admin', isAdmin: true);
        $this->regularUser = $this->createUser('test-user', isAdmin: false);
    }

    public function tearDown(): void
    {
        $this->cleanAllWorkspaces();

        parent::tearDown();
    }

    public static function provideTypes(): array
    {
        return [
            'object'   => ['object'],
            'document' => ['document'],
            'asset'    => ['asset'],
        ];
    }

    /**
     * Admin always gets full access regardless of workspace entries.
     *
     * @dataProvider provideTypes
     */
    public function testAdminAlwaysAllowed(string $type): void
    {
        $result = $this->findForbiddenPaths($type, $this->adminUser);

        $this->assertSame([], $result['forbidden']);
        $this->assertSame(['/'], $result['allowed']);
    }

    /**
     * A user with no roles uses the simple single-query branch.
     * Verifies both allowed and forbidden paths resolve correctly without roles.
     *
     * @dataProvider provideTypes
     */
    public function testUserWithNoRoles(string $type): void
    {
        $this->assertEmpty($this->regularUser->getRoles());

        $cidParent = $this->createElementForType($type);
        $cidChild = $this->createElementForType($type);
        $this->addWorkspace($type, $this->regularUser->getId(), '/foo', $cidParent, 0);
        $this->addWorkspace($type, $this->regularUser->getId(), '/foo/bar', $cidChild, 1);

        $result = $this->findForbiddenPaths($type, $this->regularUser);

        $this->assertArrayHasKey('/foo', $result['forbidden']);
        $this->assertContains('/foo/bar', $result['forbidden']['/foo']);
        $this->assertContains('/foo/bar', $result['allowed']);
    }

    /**
     * A user with no workspace entries gets everything forbidden.
     *
     * @dataProvider provideTypes
     */
    public function testNoWorkspacesReturnsForbiddenRoot(string $type): void
    {
        $result = $this->findForbiddenPaths($type, $this->regularUser);

        $this->assertArrayHasKey('/', $result['forbidden']);
        $this->assertSame([], $result['forbidden']['/']);
        $this->assertSame([], $result['allowed']);
    }

    /**
     * A single allowed path is returned in the allowed list.
     *
     * @dataProvider provideTypes
     */
    public function testSingleAllowedPath(string $type): void
    {
        $cid = $this->createElementForType($type);

        $this->addWorkspace($type, $this->regularUser->getId(), '/foo', $cid, 1);

        $result = $this->findForbiddenPaths($type, $this->regularUser);

        $this->assertContains('/foo', $result['allowed']);
        $this->assertArrayNotHasKey('/foo', $result['forbidden']);
    }

    /**
     * A single forbidden path with no allowed children yields an empty
     * exceptions list.
     *
     * @dataProvider provideTypes
     */
    public function testSingleForbiddenPathNoChildren(string $type): void
    {
        $this->addWorkspace($type, $this->regularUser->getId(), '/foo', $this->createElementForType($type), 0);

        $result = $this->findForbiddenPaths($type, $this->regularUser);

        $this->assertArrayHasKey('/foo', $result['forbidden']);
        $this->assertSame([], $result['forbidden']['/foo']);
    }

    /**
     * A forbidden parent with an explicitly allowed child must list that
     * child inside the parent's exceptions array.
     *
     * @dataProvider provideTypes
     */
    public function testForbiddenParentWithAllowedChild(string $type): void
    {
        $this->addWorkspace($type, $this->regularUser->getId(), '/foo', $this->createElementForType($type), 0);
        $this->addWorkspace($type, $this->regularUser->getId(), '/foo/bar', $this->createElementForType($type), 1);

        $result = $this->findForbiddenPaths($type, $this->regularUser);

        $this->assertArrayHasKey('/foo', $result['forbidden']);
        $this->assertContains('/foo/bar', $result['forbidden']['/foo']);
        $this->assertContains('/foo/bar', $result['allowed']);
    }

    /**
     * Multiple independent allowed paths all appear in the allowed list.
     *
     * @dataProvider provideTypes
     */
    public function testMultipleAllowedPaths(string $type): void
    {
        $this->addWorkspace($type, $this->regularUser->getId(), '/a', $this->createElementForType($type), 1);
        $this->addWorkspace($type, $this->regularUser->getId(), '/b', $this->createElementForType($type), 1);
        $this->addWorkspace($type, $this->regularUser->getId(), '/c', $this->createElementForType($type), 1);

        $result = $this->findForbiddenPaths($type, $this->regularUser);

        $this->assertContains('/a', $result['allowed']);
        $this->assertContains('/b', $result['allowed']);
        $this->assertContains('/c', $result['allowed']);
        $this->assertEmpty($result['forbidden']);
    }

    /**
     * A forbidden child beneath an allowed parent is tracked independently.
     *
     * @dataProvider provideTypes
     */
    public function testAllowedParentForbiddenChild(string $type): void
    {
        $this->addWorkspace($type, $this->regularUser->getId(), '/foo', $this->createElementForType($type), 1);
        $this->addWorkspace($type, $this->regularUser->getId(), '/foo/secret', $this->createElementForType($type), 0);

        $result = $this->findForbiddenPaths($type, $this->regularUser);

        $this->assertContains('/foo', $result['allowed']);
        $this->assertArrayHasKey('/foo/secret', $result['forbidden']);
        $this->assertSame([], $result['forbidden']['/foo/secret']);
    }

    /**
     * A path allowed via role is returned in the allowed list when the user
     * has no direct workspace entry for that path.
     *
     * @dataProvider provideTypes
     */
    public function testRoleAllowedPath(string $type): void
    {
        $role = $this->createRole('test-role-allowed');
        $this->regularUser->setRoles([$role->getId()]);
        $this->regularUser->save();

        $this->addWorkspace($type, $role->getId(), '/role-path', $this->createElementForType($type), 1);

        $result = $this->findForbiddenPaths($type, $this->regularUser);

        $this->assertContains('/role-path', $result['allowed']);
    }

    /**
     * User permission (list=0) must win over a conflicting role permission
     * (list=1) on the same path.
     *
     * @dataProvider provideTypes
     */
    public function testUserPermissionWinsOverRoleForForbidden(string $type): void
    {
        $role = $this->createRole('test-role-conflict-forbidden');
        $this->regularUser->setRoles([$role->getId()]);
        $this->regularUser->save();

        $cid = $this->createElementForType($type);

        $this->addWorkspace($type, $role->getId(), '/contested', $cid, 1);
        $this->addWorkspace($type, $this->regularUser->getId(), '/contested', $cid, 0);

        $result = $this->findForbiddenPaths($type, $this->regularUser);

        $this->assertArrayHasKey('/contested', $result['forbidden']);
        $this->assertNotContains('/contested', $result['allowed']);
    }

    /**
     * User permission (list=1) must win over a conflicting role permission
     * (list=0) on the same path.
     *
     * @dataProvider provideTypes
     */
    public function testUserPermissionWinsOverRoleForAllowed(string $type): void
    {
        $role = $this->createRole('test-role-conflict-allowed');
        $this->regularUser->setRoles([$role->getId()]);
        $this->regularUser->save();

        $cid = $this->createElementForType($type);

        $this->addWorkspace($type, $role->getId(), '/contested', $cid, 0);
        $this->addWorkspace($type, $this->regularUser->getId(), '/contested', $cid, 1);

        $result = $this->findForbiddenPaths($type, $this->regularUser);

        $this->assertContains('/contested', $result['allowed']);
        $this->assertArrayNotHasKey('/contested', $result['forbidden']);
    }

    /**
     * When multiple roles define the same path, MAX(list) wins — list=1
     * takes precedence over list=0 at role level.
     *
     * @dataProvider provideTypes
     */
    public function testMultipleRolesMaxListWins(string $type): void
    {
        $roleA = $this->createRole('test-role-multi-a');
        $roleB = $this->createRole('test-role-multi-b');

        $this->regularUser->setRoles([$roleA->getId(), $roleB->getId()]);
        $this->regularUser->save();

        $cid = $this->createElementForType($type);

        $this->addWorkspace($type, $roleA->getId(), '/shared', $cid, 0);
        $this->addWorkspace($type, $roleB->getId(), '/shared', $cid, 1);

        $result = $this->findForbiddenPaths($type, $this->regularUser);

        // No user-level override → roles resolved via MAX → list=1 wins.
        $this->assertContains('/shared', $result['allowed']);
    }

    /**
     * Allowed children of a forbidden path must not leak into sibling paths.
     *
     * @dataProvider provideTypes
     */
    public function testForbiddenPathDoesNotLeakIntoSiblings(string $type): void
    {
        $this->addWorkspace($type, $this->regularUser->getId(), '/a', $this->createElementForType($type), 0);
        $this->addWorkspace($type, $this->regularUser->getId(), '/a/child', $this->createElementForType($type), 1);
        $this->addWorkspace($type, $this->regularUser->getId(), '/b', $this->createElementForType($type), 1);

        $result = $this->findForbiddenPaths($type, $this->regularUser);

        $this->assertArrayHasKey('/a', $result['forbidden']);
        $this->assertContains('/a/child', $result['forbidden']['/a']);
        $this->assertNotContains('/b', $result['forbidden']['/a']);
        $this->assertContains('/b', $result['allowed']);
    }

    /**
     * A deeply nested allowed child is captured as an exception of every
     * forbidden ancestor in the chain.
     *
     * @dataProvider provideTypes
     */
    public function testDeeplyNestedAllowedChildUnderForbiddenAncestors(string $type): void
    {
        $this->addWorkspace($type, $this->regularUser->getId(), '/root', $this->createElementForType($type), 0);
        $this->addWorkspace($type, $this->regularUser->getId(), '/root/a', $this->createElementForType($type), 0);
        $this->addWorkspace($type, $this->regularUser->getId(), '/root/a/b', $this->createElementForType($type), 1);

        $result = $this->findForbiddenPaths($type, $this->regularUser);

        $this->assertArrayHasKey('/root', $result['forbidden']);
        $this->assertArrayHasKey('/root/a', $result['forbidden']);
        $this->assertContains('/root/a/b', $result['forbidden']['/root/a']);
        $this->assertContains('/root/a/b', $result['forbidden']['/root']);
    }

    /**
     * A forbidden grandchild beneath an allowed child beneath a forbidden parent
     * is tracked as its own forbidden entry.
     *
     * @dataProvider provideTypes
     */
    public function testForbiddenGrandchildUnderAllowedChildUnderForbiddenParent(string $type): void
    {
        $this->addWorkspace($type, $this->regularUser->getId(), '/foo', $this->createElementForType($type), 0);
        $this->addWorkspace($type, $this->regularUser->getId(), '/foo/bar', $this->createElementForType($type), 1);
        $this->addWorkspace($type, $this->regularUser->getId(), '/foo/bar/baz', $this->createElementForType($type), 0);

        $result = $this->findForbiddenPaths($type, $this->regularUser);

        $this->assertArrayHasKey('/foo', $result['forbidden']);
        $this->assertContains('/foo/bar', $result['forbidden']['/foo']);
        $this->assertContains('/foo/bar', $result['allowed']);
        $this->assertArrayHasKey('/foo/bar/baz', $result['forbidden']);
        $this->assertSame([], $result['forbidden']['/foo/bar/baz']);
    }

    /**
     * A path that shares a suffix with a forbidden path but is not a child of it
     * must not be treated as a child of that path.
     * Example: '/baz/foo' is not a child of '/foo'.
     *
     * @dataProvider provideTypes
     */
    public function testSiblingPathWithSameSuffixIsNotTreatedAsChild(string $type): void
    {
        $this->addWorkspace($type, $this->regularUser->getId(), '/foo', $this->createElementForType($type), 0);
        $this->addWorkspace($type, $this->regularUser->getId(), '/baz/foo', $this->createElementForType($type), 1);

        $result = $this->findForbiddenPaths($type, $this->regularUser);

        $this->assertArrayHasKey('/foo', $result['forbidden']);
        $this->assertNotContains('/baz/foo', $result['forbidden']['/foo']);
        $this->assertContains('/baz/foo', $result['allowed']);
    }

    private function createUser(string $name, bool $isAdmin): User
    {
        if (!$user = User::getByName($name)) {
            $user = new User();
            $user->setName($name);
            $user->setAdmin($isAdmin);
            $user->save();
        }

        return $user;
    }

    private function createRole(string $name): User\Role
    {
        if (!$role = User\Role::getByName($name)) {
            $role = new User\Role();
            $role->setName($name);
            $role->save();
        }

        return $role;
    }

    /**
     * Creates a real persisted element of the given type and returns its ID,
     * satisfying the FK constraint on users_workspaces_*.cid.
     */
    private function createElementForType(string $type): int
    {
        $key = 'test-path-element-' . uniqid('', false);

        return match ($type) {
            'object' => TestHelper::createEmptyObject($key, true)->getId(),
            'asset' => (static function () use ($key): int {
                $asset = new Asset();
                $asset->setParent(Asset::getByPath('/'));
                $asset->setKey($key);
                $asset->save();

                return $asset->getId();
            })(),
            'document' => (static function () use ($key): int {
                $document = new Page();
                $document->setKey($key);
                $document->setParentId(1);
                $document->setPublished(true);
                $document->save();

                return $document->getId();
            })(),

            default => throw new \InvalidArgumentException("Unknown type: $type"),
        };
    }

    private function addWorkspace(string $type, int $userId, string $cpath, int $cid, int $list): void
    {
        Db::get()->insert(
            sprintf('users_workspaces_%s', $type),
            ['userId' => $userId, 'cpath' => $cpath, 'cid' => $cid, 'list' => $list]
        );
    }

    private function cleanAllWorkspaces(): void
    {
        $db = Db::get();
        foreach (['object', 'document', 'asset'] as $type) {
            $db->executeStatement(sprintf('DELETE FROM users_workspaces_%s', $type));
        }
    }

    private function findForbiddenPaths(string $type, User $user): array
    {
        return Service::findForbiddenPaths($type, $user);
    }

}
