<?php

namespace CultuurNet\UDB3\Symfony\Management;

use CultuurNet\SymfonySecurityJwt\Authentication\JwtUserToken;
use PHPUnit_Framework_MockObject_MockObject;
use Lcobucci\JWT\Claim\Basic as BasicClaim;
use Lcobucci\JWT\Token as JwtToken;

trait TokenMockingTrait
{
    /**
     * @param string $userId
     *
     * @return JwtUserToken|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockToken($userId)
    {
        $token = $this->getMock(
            JwtUserToken::class,
            ['isAuthenticated', 'getCredentials'],
            array(),
            'JwtUserToken',
            false
        );

        $jwtCredentials = new JwtToken(
            ['alg' => 'none'],
            ['uid' => new BasicClaim('uid', $userId)]
        );

        $token
            ->method('isAuthenticated')
            ->willReturn(true);

        $token
            ->method('getCredentials')
            ->willReturn($jwtCredentials);

        return $token;
    }
}
