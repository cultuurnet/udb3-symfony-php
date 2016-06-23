<?php

namespace CultuurNet\UDB3\Symfony\Label;

use CultuurNet\UDB3\Label\Services\WriteResult;
use CultuurNet\UDB3\Label\Services\WriteServiceInterface;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Symfony\Label\Helper\CommandType;
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
            RequestHelper::PRIVACY => 'private',
        ];
        $this->request = $this->createRequestWithContent($this->contentAsArray);

        $this->commandId = new UUID();
        $this->uuid = new UUID();

        $this->writeService = $this->getMock(WriteServiceInterface::class);
        $this->mockCreate();
        $this->mockMakeVisible();
        $this->mockMakeInvisible();
        $this->mockMakePublic();
        $this->mockMakePrivate();

        $this->requestHelper = $this->getMock(RequestHelper::class);
        $this->mockGetName();
        $this->mockGetVisibility();
        $this->mockGetPrivacy();
        $this->mockGetCommandType();

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

    /**
     * @test
     * @dataProvider provider
     * @param array $contentAsArray
     * @param $method
     */
    public function it_handles_patch(
        array $contentAsArray,
        $method
    ) {
        $request = $this->createRequestWithContent($contentAsArray);

        $this->writeService->expects($this->once())
            ->method($method);

        $jsonResponse = $this->editRestController->patch($request, $this->uuid);

        $expectedJsonResponse = new JsonResponse([
            EditRestController::COMMAND_ID => $this->commandId->toNative(),
            EditRestController::UUID => $this->uuid->toNative()
        ]);

        $this->assertEquals($expectedJsonResponse, $jsonResponse);
    }

    public function provider()
    {
        return [
            [[RequestHelper::COMMAND => 'MakeVisible'], 'MakeVisible'],
            [[RequestHelper::COMMAND => 'MakeInvisible'], 'MakeInvisible'],
            [[RequestHelper::COMMAND => 'MakePublic'], 'MakePublic'],
            [[RequestHelper::COMMAND => 'MakePrivate'], 'MakePrivate']
        ];
    }

    private function mockCreate()
    {
        $this->writeService->method('create')
            ->with(
                new StringLiteral($this->contentAsArray[RequestHelper::NAME]),
                Visibility::fromNative($this->contentAsArray[RequestHelper::VISIBILITY]),
                Privacy::fromNative($this->contentAsArray[RequestHelper::PRIVACY])
            )
            ->willReturn(new WriteResult($this->commandId, $this->uuid));
    }

    private function mockMakeVisible()
    {
        $this->writeService->method('MakeVisible')
            ->with($this->uuid)
            ->willReturn(new WriteResult($this->commandId, $this->uuid));
    }

    private function mockMakeInvisible()
    {
        $this->writeService->method('MakeInvisible')
            ->with($this->uuid)
            ->willReturn(new WriteResult($this->commandId, $this->uuid));
    }

    private function mockMakePublic()
    {
        $this->writeService->method('MakePublic')
            ->with($this->uuid)
            ->willReturn(new WriteResult($this->commandId, $this->uuid));
    }

    private function mockMakePrivate()
    {
        $this->writeService->method('MakePrivate')
            ->with($this->uuid)
            ->willReturn(new WriteResult($this->commandId, $this->uuid));
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

    private function mockGetCommandType()
    {
        $this->requestHelper->method('getCommandType')
            ->willReturnCallback(
                function (Request $request) {
                    $bodyAsArray = json_decode($request->getContent(), true);
                    return CommandType::fromNative($bodyAsArray['command']);
                }
            );
    }

    /**
     * @param array $contentAsArray
     * @return Request
     */
    private function createRequestWithContent(array $contentAsArray)
    {
        return new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode($contentAsArray)
        );
    }
}
