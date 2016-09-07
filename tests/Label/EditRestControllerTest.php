<?php

namespace CultuurNet\UDB3\Symfony\Label;

use CultuurNet\UDB3\Label\Services\WriteResult;
use CultuurNet\UDB3\Label\Services\WriteServiceInterface;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
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
     * @var EditRestController
     */
    private $editRestController;

    protected function setUp()
    {
        $this->contentAsArray = [
            EditRestController::NAME => 'labelName',
            EditRestController::VISIBILITY => 'invisible',
            EditRestController::PRIVACY => 'private',
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


        $this->editRestController = new EditRestController($this->writeService);
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
            [['command' => 'MakeVisible'], 'makeVisible'],
            [['command' => 'MakeInvisible'], 'makeInvisible'],
            [['command' => 'MakePublic'], 'makePublic'],
            [['command' => 'MakePrivate'], 'makePrivate']
        ];
    }

    private function mockCreate()
    {
        $this->writeService->method('create')
            ->with(
                new LabelName($this->contentAsArray[EditRestController::NAME]),
                Visibility::fromNative($this->contentAsArray[EditRestController::VISIBILITY]),
                Privacy::fromNative($this->contentAsArray[EditRestController::PRIVACY])
            )
            ->willReturn(new WriteResult($this->commandId, $this->uuid));
    }

    private function mockMakeVisible()
    {
        $this->writeService->method('makeVisible')
            ->with($this->uuid)
            ->willReturn(new WriteResult($this->commandId, $this->uuid));
    }

    private function mockMakeInvisible()
    {
        $this->writeService->method('makeInvisible')
            ->with($this->uuid)
            ->willReturn(new WriteResult($this->commandId, $this->uuid));
    }

    private function mockMakePublic()
    {
        $this->writeService->method('makePublic')
            ->with($this->uuid)
            ->willReturn(new WriteResult($this->commandId, $this->uuid));
    }

    private function mockMakePrivate()
    {
        $this->writeService->method('makePrivate')
            ->with($this->uuid)
            ->willReturn(new WriteResult($this->commandId, $this->uuid));
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
