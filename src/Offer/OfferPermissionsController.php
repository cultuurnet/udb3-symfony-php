<?php
namespace CultuurNet\UDB3\Symfony\Offer;

use CultuurNet\UDB3\Offer\Security\Permission\PermissionVoterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\StringLiteral\StringLiteral;

class OfferPermissionsController
{
    /**
     * @var array
     */
    private $permissions;

    /**
     * @var PermissionVoterInterface
     */
    private $permissionVoter;

    /**
     * @var StringLiteral
     */
    private $currentUserId;

    /**
     * @param array $permissions
     * @param PermissionVoterInterface $permissionVoter
     * @param StringLiteral|null $currentUserId
     */
    public function __construct(
        array $permissions,
        PermissionVoterInterface $permissionVoter,
        StringLiteral $currentUserId = null
    ) {
        $this->permissions = $permissions;
        $this->permissionVoter = $permissionVoter;
        $this->currentUserId = $currentUserId;
    }

    /**
     * @param string $offerId
     * @return Response
     */
    public function getPermissionsForCurrentUser($offerId)
    {
        return $this->getPermissions(
            new StringLiteral((string) $offerId),
            $this->currentUserId
        );
    }

    /**
     * @param string $offerId
     * @param string $userId
     * @return Response
     */
    public function getPermissionsForGivenUser($offerId, $userId)
    {
        return $this->getPermissions(
            new StringLiteral((string) $offerId),
            new StringLiteral((string) $userId)
        );
    }

    /**
     * @param StringLiteral $offerId
     * @param StringLiteral|null $userId
     * @return Response
     */
    private function getPermissions($offerId, $userId = null)
    {
        $permissionsToReturn = array();
        foreach ($this->permissions as $permission) {
            if ($userId) {
                $hasPermission = $this->permissionVoter->isAllowed(
                    $permission,
                    $offerId,
                    $userId
                );

                if ($hasPermission) {
                    $permissionsToReturn[] = $permission->__toString();
                }
            }
        }

        return JsonResponse::create(['permissions' => $permissionsToReturn])
            ->setPrivate();
    }
}
