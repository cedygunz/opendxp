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

namespace OpenDxp\Security\Voter;

use OpenDxp\Model\Element\ElementInterface;
use OpenDxp\Security\PermissionAttribute;
use OpenDxp\Security\User\TokenStorageUserResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ElementPermissionVoter extends Voter
{
    public function __construct(private readonly TokenStorageUserResolver $tokenResolver,)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof ElementInterface && str_starts_with($attribute, PermissionAttribute::ELEMENT_PREFIX);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $this->tokenResolver->getUser();

        return
            $user !== null &&
            $subject instanceof ElementInterface &&
            $subject->isAllowed(PermissionAttribute::for($attribute), $user);
    }
}
