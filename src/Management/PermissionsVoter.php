<?php

namespace CultuurNet\UDB3\Symfony\Management;

use CultuurNet\SymfonySecurityJwt\Authentication\JwtUserToken;
use CultuurNet\UDB3\Role\ValueObjects\Permission;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class PermissionsVoter implements VoterInterface
{
    /**
     * @var string[]
     */
    private $authorizationList;

    /**
     * PermissionVoter constructor.
     * @param string[] $authorizationList
     */
    public function __construct($authorizationList)
    {
        $this->authorizationList = $authorizationList;
    }

    public function supportsAttribute($attribute)
    {
        return Permission::has($attribute);
    }

    public function supportsClass($class)
    {
        return true;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = self::ACCESS_ABSTAIN;

        if (!($object instanceof Request)) {
            return $result;
        }

        if ($token instanceof JwtUserToken && $token->isAuthenticated()) {
            $userUuid = $token->getCredentials()->getClaim('uid');
        } else {
            return $result;
        }

        foreach ($attributes as $attribute) {
            // these attributes come from the access control rules in the security configuration
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            if (in_array(
                $userUuid,
                $this->authorizationList['allow_all']
            )) {
                $result = self::ACCESS_GRANTED;
            } else {
                $result = self::ACCESS_DENIED;
            }
        }

        return $result;
    }
}
