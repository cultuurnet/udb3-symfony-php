<?php

namespace CultuurNet\UDB3\Symfony\Label;

use CultuurNet\UDB3\Label\Services\WriteResult;
use CultuurNet\UDB3\Label\Services\WriteServiceInterface;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Symfony\Label\Helper\RequestHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class EditRestControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $contentAsArray;

    /**
     * @var UUID
     */
    private $commandId;

    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var WriteServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $writeService;

    /**
     * @var RequestHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestHelper;

    /**
     * @var EditRestController
     */
    private $editRestController;

    protected function setUp()
    {
        $this->contentAsArray = [
            RequestHelper::NAME => 'labelName',
            RequestHelper::VISIBILITY => 'invisible',
            RequestHelper::PRIVACY => 'private'
        ];
        $this->request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode($this->contentAsArray)
        );

        $this->commandId = new UUID();
        $this->uuid = new UUID();

        $this->writeService = $this->getMock(WriteServiceInterface::class);
        $this->mockCreate();

        $this->requestHelper = $this->getMock(RequestHelper::class);
        $this->mockGetName();
        $this->mockGetVisibility();
        $this->mockGetPrivacy();

        $this->editRestController = new EditRestController(
            $this->writeService,
            $this->requestHelper
        );
    }

    /**
     * @test
     */
    public function it_returns_json_response_for_create()
    {
        $jsonResponse = $this->editRestController->create($this->request);

        $expectedJsonResponse = new JsonResponse([
            EditRestController::COMMAND_ID => $this->commandId->toNative(),
            EditRestController::UUID => $this->uuid->toNative()
        ]);

        $this->assertEquals($expectedJsonResponse, $jsonResponse);
    }

    private function mockCreate()
    {
        $this->writeService->method('create')
            ->with(
                new StringLiteral($this->contentAsArray[RequestHelper::NAME]),
                Visibility::fromNative($this->contentAsArray[RequestHelper::VISIBILITY]),
                Privacy::fromNative($this->contentAsArray[RequestHelper::PRIVACY])
            )
            ->willReturn(new WriteResult(
                $this->commandId,
                $this->uuid
            ));
    }

    private function mockGetName()
    {
        $this->requestHelper->method('getName')
            ->with($this->request)
            ->willReturn(
                new StringLiteral($this->contentAsArray[RequestHelper::NAME])
            );
    }

    private function mockGetVisibility()
    {
        $this->requestHelper->method('getVisibility')
            ->with($this->request)
            ->willReturn(
                Visibility::fromNative(
                    $this->contentAsArray[RequestHelper::VISIBILITY]
                )
            );
    }

    private function mockGetPrivacy()
    {
        $this->requestHelper->method('getPrivacy')
            ->with($this->request)
            ->willReturn(
                Privacy::fromNative(
                    $this->contentAsArray[RequestHelper::PRIVACY]
                )
            );
    }
}
