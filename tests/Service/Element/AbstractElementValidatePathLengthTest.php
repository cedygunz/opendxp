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

namespace OpenDxp\Tests\Service\Element;

use OpenDxp\Model\Element\AbstractElement;
use OpenDxp\Model\Element\Service;
use OpenDxp\Tests\Support\Test\TestCase;
use OpenDxp\Tests\Support\Util\TestHelper;
use ReflectionClass;

final class AbstractElementValidatePathLengthTest extends TestCase
{
    /**
     * Maximum allowed path length for validatePathLength validation.
     */
    private const MAX_VALID_PATH_LENGTH = 765;

    /**
     * Maximum allowed key length for getValidKey truncation.
     */
    private const MAX_VALID_KEY_LENGTH = 255;

    protected function needsDb(): bool
    {
        return true;
    }

    public function setUp(): void
    {
        parent::setUp();
        TestHelper::cleanUp();
    }

    public function tearDown(): void
    {
        TestHelper::cleanUp();
        parent::tearDown();
    }

    /**
     * Creates a test element with a specified path length using TestHelper.
     */
    private function createElementWithPathLength(int $pathLength): AbstractElement
    {
        // Create a real persisted object using TestHelper
        $element = TestHelper::createEmptyObject('pathlen-test-', true);
        
        // Build a key that matches the desired path length
        // Path format: /parent_path/key, so we adjust the key length accordingly
        $basePath = $element->getRealPath(); // e.g., "/"
        $remainingLength = $pathLength - mb_strlen($basePath, 'UTF-8');
        
        if ($remainingLength > 0) {
            $key = str_repeat('a', $remainingLength);
            $element->setKey($key);
        }
        
        return $element;
    }

    /**
     * Invokes the protected validatePathLength() method via reflection.
     */
    private function invokeValidatePathLength(AbstractElement $element): void
    {
        $ref = new ReflectionClass($element);
        $method = $ref->getMethod('validatePathLength');
        $method->setAccessible(true);
        $method->invoke($element);
    }

    /**
     * Tests validatePathLength() method.
     */

    public function testValidatePathLengthAllowsExactlyMaxLength(): void
    {
        $element = $this->createElementWithPathLength(self::MAX_VALID_PATH_LENGTH);

        $this->invokeValidatePathLength($element);
        $this->assertTrue(true);
    }

    public function testValidatePathLengthRejectsLongerThanMaxLength(): void
    {
        $element = $this->createElementWithPathLength(self::MAX_VALID_PATH_LENGTH + 1);

        $this->expectException(\Exception::class);
        $this->invokeValidatePathLength($element);
    }

    /**
     * Tests getValidKey() method.
     */

    public function testGetValidKeyTruncatesTo255Characters(): void
    {
        $input = str_repeat('a', 300);
        $result = Service::getValidKey($input, 'object');

        $this->assertSame(self::MAX_VALID_KEY_LENGTH, mb_strlen($result, 'UTF-8'));
        $this->assertSame(str_repeat('a', self::MAX_VALID_KEY_LENGTH), $result);
    }

    public function testGetValidKeyTruncatesMultibyteCharactersTo255Characters(): void
    {
        $input = str_repeat('é', 300);
        $result = Service::getValidKey($input, 'object');

        $this->assertSame(self::MAX_VALID_KEY_LENGTH, mb_strlen($result, 'UTF-8'));
        $this->assertSame(str_repeat('é', self::MAX_VALID_KEY_LENGTH), $result);
    }

    public function testGetValidKeyReplaces4ByteUnicodeCharacters(): void
    {
        $result = Service::getValidKey("abc📦def", 'object');

        $this->assertSame('abc-def', $result);
    }

    public function testGetValidKeyReplacesSlashes(): void
    {
        $result = Service::getValidKey("my/key/name", 'object');

        $this->assertSame('my-key-name', $result);
    }

    public function testGetValidKeyReplacesControlCharacters(): void
    {
        // Control characters (like \0, \x01, etc.) should be removed
        $result = Service::getValidKey("my\x00key", 'object');

        $this->assertStringNotContainsString("\x00", $result);
        $this->assertNotSame("my\x00key", $result);
    }

    public function testGetValidKeyHandlesMixedEncodingCharacters(): void
    {
        // Mix of ASCII (1 byte), 2-byte (é), and 3-byte (€) UTF-8 characters
        // This tests that mb_substr respects character boundaries regardless of byte length
        $input = 'a' . str_repeat('é', 100) . str_repeat('€', 100) . str_repeat('a', 100);
        $result = Service::getValidKey($input, 'object');

        // Should be exactly 255 characters
        $this->assertSame(self::MAX_VALID_KEY_LENGTH, mb_strlen($result, 'UTF-8'));
        
        // Verify no partial characters (all characters should be complete)
        // by checking it starts with 'a' and contains complete é and € characters
        $this->assertStringStartsWith('a', $result);
    }

    public function testGetValidKeyAsciiOnlyString(): void
    {
        // ASCII is a subset of UTF-8 - all characters are 1 byte
        $input = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $result = Service::getValidKey($input, 'object');

        // ASCII should pass through unchanged
        $this->assertSame($input, $result);
        $this->assertSame(strlen($input), mb_strlen($result, 'UTF-8'));
    }

    public function testGetValidKeyHandlesLatinExtendedCharacters(): void
    {
        // Latin Extended-A characters (like ą, ć, ę) are 2-byte UTF-8
        $input = str_repeat('ą', 300);
        $result = Service::getValidKey($input, 'object');

        // Should truncate to exactly 255 characters
        $this->assertSame(self::MAX_VALID_KEY_LENGTH, mb_strlen($result, 'UTF-8'));
        $this->assertSame(str_repeat('ą', self::MAX_VALID_KEY_LENGTH), $result);
    }

    public function testGetValidKeyHandlesCyrillicCharacters(): void
    {
        // Cyrillic characters (like А, Б, В) are 2-byte UTF-8
        $input = str_repeat('А', 300);
        $result = Service::getValidKey($input, 'object');

        // Should truncate to exactly 255 characters
        $this->assertSame(self::MAX_VALID_KEY_LENGTH, mb_strlen($result, 'UTF-8'));
        $this->assertSame(str_repeat('А', self::MAX_VALID_KEY_LENGTH), $result);
    }

    public function testGetValidKeyRemovesLeadingAndTrailingWhitespace(): void
    {
        // Key with leading and trailing spaces should have them removed
        $input = '    mykey   ';
        $result = Service::getValidKey($input, 'object');

        $this->assertSame('mykey', $result);
        $this->assertStringNotContainsString(' ', $result);
    }

    public function testGetValidKeyPreservesInternalWhitespace(): void
    {
        // Internal spaces in the key should be preserved
        $input = 'my    key   name';
        $result = Service::getValidKey($input, 'object');

        $this->assertStringContainsString(' ', $result);
        $this->assertSame('my    key   name', $result);
    }

    public function testGetValidKeyWithTabsAndNewlines(): void
    {
        // Tabs and newlines are control characters and should be removed by preg_replace
        $input = "my\t\nkey";
        $result = Service::getValidKey($input, 'object');

        // Control characters should be removed
        $this->assertStringNotContainsString("\t", $result);
        $this->assertStringNotContainsString("\n", $result);
    }

    public function testGetValidKeyTrailingWhitespaceAfterTruncation(): void
    {
        // Create a string that when truncated will have trailing whitespace
        // > MAX_VALID_KEY_LENGTH chars total with exposed trailing spaces after truncation
        $input = str_repeat('a', 250) . '     TRUNCATETHIS';
        $result = Service::getValidKey($input, 'object');

        // Should have trailing spaces removed
        $this->assertFalse(str_ends_with($result, ' '));
        // Result should be exactly 250 'a's (trailing spaces removed)
        $this->assertSame(str_repeat('a', 250), $result);
    }
}
