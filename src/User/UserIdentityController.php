<?php

namespace CultuurNet\UDB3\Symfony\User;

use Crell\ApiProblem\ApiProblem;
use CultuurNet\UDB3\Symfony\HttpFoundation\ApiProblemJsonResponse;
use CultuurNet\UDB3\Symfony\JsonLdResponse;
use CultuurNet\UDB3\UiTID\UsersInterface;
use CultuurNet\UDB3\User\CultureFeedUserIdentityDetailsFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use ValueObjects\Exception\InvalidNativeArgumentException;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\EmailAddress;

class UserIdentityController
{
    /**
     * @var \ICultureFeed
     */
    private $cultureFeed;

    /**
     * @var UsersInterface
     */
    private $userIdResolver;

    /**
     * @param \ICultureFeed $cultureFeed
     * @param UsersInterface $userIdResolver
     * @param CultureFeedUserIdentityDetailsFactoryInterface $cfUserIdentityFactory
     */
    public function __construct(
        \ICultureFeed $cultureFeed,
        UsersInterface $userIdResolver,
        CultureFeedUserIdentityDetailsFactoryInterface $cfUserIdentityFactory
    ) {
        $this->cultureFeed = $cultureFeed;
        $this->userIdResolver = $userIdResolver;
        $this->cfUserIdentityFactory = $cfUserIdentityFactory;
    }

    /**
     * @param string $userId
     * @return Response
     */
    public function getByUserId($userId)
    {
        $userId = new StringLiteral((string) $userId);
        return $this->getUserIdentityResponse($userId);
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

        $userId = $this->userIdResolver->byEmail($emailAddress);

        if (is_null($userId)) {
            return $this->getUserNotFoundResponse();
        }

        return $this->getUserIdentityResponse($userId);
    }

    /**
     * @param StringLiteral $userId
     * @return Response
     */
    private function getUserIdentityResponse(StringLiteral $userId)
    {
        $cfUser = $this->cultureFeed->getUser($userId->toNative());

        if (is_null($cfUser)) {
            return $this->getUserNotFoundResponse();
        }

        $userIdentity = $this->cfUserIdentityFactory->fromCultureFeedUser(
            $cfUser
        );

        return (new JsonLdResponse())
            ->setData($userIdentity)
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
