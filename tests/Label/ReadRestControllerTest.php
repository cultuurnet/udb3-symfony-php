<?php

namespace CultuurNet\UDB3\Symfony\Label;

use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\Services\ReadServiceInterface;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Symfony\Label\Helper\RequestHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class ReadRestControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Entity
     */
    private $entity;

    /**
     * @var ReadServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readService;

    /**
     * @var ReadRestController
     */
    private $readRestController;

    protected function setUp()
    {
        $this->entity = new Entity(
            new UUID(),
            new StringLiteral('labelName'),
            Visibility::INVISIBLE(),
            Privacy::PRIVACY_PRIVATE()
        );

        $this->readService = $this->getMock(ReadServiceInterface::class);
        $this->mockGetByUuid();

        $this->readRestController = new ReadRestController($this->readService);
    }

    /**
     * @test
     */
    public function it_returns_json_response_for_get_by_uuid()
    {
        $jsonResponse = $this->readRestController->getByUuid(
            $this->entity->getUuid()
        );

        $expectedJsonResponse = new JsonResponse([
            ReadRestController::ID => $this->entity->getUuid()->toNative(),
            ReadRestController::NAME => $this->entity->getName()->toNative(),
            ReadRestController::VISIBILITY => $this->entity->getVisibility()->toNative(),
            ReadRestController::PRIVACY => $this->entity->getPrivacy()->toNative()
        ]);

        $this->assertEquals($expectedJsonResponse, $jsonResponse);
    }

    private function mockGetByUuid()
    {
        $this->readService->method('getByUuid')
            ->with($this->entity->getUuid()->toNative())
            ->willReturn($this->entity);
    }
}
