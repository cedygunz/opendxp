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

namespace OpenDxp\Helper;

/**
 * Keep in sync with bundles/AdminBundle/public/js/opendxp/object/helpers/reservedWords.js
 */
class ReservedWordsHelper
{
    public const array PHP_KEYWORDS = [
        'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue',
        'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach',
        'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'finally', 'fn', 'for', 'foreach',
        'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof',
        'interface', 'isset', 'list', 'match', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public',
        'readonly', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use',
        'var', 'while', 'xor', 'yield', 'yield_from',
    ];

    public const array PHP_CLASSES = [
        'self', 'static', 'parent',
    ];

    public const array PHP_OTHER_WORDS = [
        'int', 'float', 'bool', 'string', 'true', 'false', 'null', 'void', 'iterable', 'object', 'mixed', 'never',
        'enum', 'resource', 'numeric',
    ];

    public const array OPENDXP = [
        'data', 'folder', 'permissions', 'dao', 'concrete', 'items',
    ];

    /**
     * @return string[]
     */
    public function getAllPhpReservedWords(): array
    {
        return [
            ...static::PHP_KEYWORDS,
            ...static::PHP_CLASSES,
            ...static::PHP_OTHER_WORDS,
        ];
    }

    /**
     * @return string[]
     */
    public function getAllReservedWords(): array
    {
        return [
            ...$this->getAllPhpReservedWords(),
            ...static::OPENDXP,
        ];
    }

    public function isReservedWord(string $word): bool
    {
        return in_array(
            strtolower($word),
            $this->getAllReservedWords(),
            true
        );
    }
}
