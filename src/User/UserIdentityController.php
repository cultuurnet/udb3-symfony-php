<?php

namespace CultuurNet\UDB3\Symfony\User;

use Crell\ApiProblem\ApiProblem;
use CultuurNet\UDB3\Symfony\HttpFoundation\ApiProblemJsonResponse;
use CultuurNet\UDB3\Symfony\JsonLdResponse;
use CultuurNet\UDB3\UiTID\UsersInterface;
use CultuurNet\UDB3\User\CultureFeedUserIdentityDetailsFactoryInterface;
use CultuurNet\UDB3\User\UserIdentityDetails;
use CultuurNet\UDB3\User\UserIdentityResolverInterface;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\Exception\InvalidNativeArgumentException;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\EmailAddress;

class UserIdentityController
{
    /**
     * @var UserIdentityResolverInterface
     */
    private $userIdentityResolver;

    /**
     * @param UserIdentityResolverInterface $userIdentityResolver
     */
    public function __construct(
        UserIdentityResolverInterface $userIdentityResolver
    ) {
        $this->userIdentityResolver = $userIdentityResolver;
    }

    /**
     * @param string $userId
     * @return Response
     */
    public function getByUserId($userId)
    {
        $userId = new StringLiteral((string) $userId);

        $userIdentity = $this->userIdentityResolver->getUserById($userId);

        return $this->getUserIdentityResponse($userIdentity);
    }

    /**
     * @param string $emailAddress
     * @return Response
     */
    public function getByEmailAddress($emailAddress)
    {
        try {
            $emailAddress = new EmailAddress((string) $emailAddress);
        } catch (InvalidNativeArgumentException $e) {
            return $this->getUserNotFoundResponse();
        }

        $userIdentity = $this->userIdentityResolver->getUserByEmail($emailAddress);

        return $this->getUserIdentityResponse($userIdentity);
    }

    /**
     * @param UserIdentityDetails|null $userIdentityDetails
     * @return Response
     */
    public function getUserIdentityResponse(UserIdentityDetails $userIdentityDetails = null)
    {
        if (is_null($userIdentityDetails)) {
            return $this->getUserNotFoundResponse();
        }

        return (new JsonLdResponse())
            ->setData($userIdentityDetails)
            ->setPrivate();
    }

    /**
     * @return ApiProblemJsonResponse
     */
    private function getUserNotFoundResponse()
    {
        return new ApiProblemJsonResponse(
            new ApiProblem('User not found.')
        );
    }
}
