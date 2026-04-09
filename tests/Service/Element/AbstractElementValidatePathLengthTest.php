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

    /**
     * Creates a test element with a specified path length.
     */
    private function createElementWithPathLength(int $pathLength): AbstractElement
    {
        return new class($pathLength) extends AbstractElement {
            private int $pathLength;

            public function __construct(int $pathLength)
            {
                $this->pathLength = $pathLength;
            }

            public function getRealFullPath(): string
            {
                return str_repeat('a', $this->pathLength);
            }
        };
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

    public function testGetValidKeyHandlesNonUtf8Input(): void
    {
        $nonUtf8 = "\x63\x61\x66\xE9";

        $result = Service::getValidKey($nonUtf8, 'object');

        $this->assertIsString($result);
        $this->assertNotSame($nonUtf8, $result);
        $this->assertNotEmpty($result);
    }

    public function testGetValidKeyReplaces4ByteUnicodeCharacters(): void
    {
        $result = Service::getValidKey("abc😀def", 'object');

        $this->assertSame('abc-def', $result);
    }
}
