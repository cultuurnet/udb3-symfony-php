<?php

namespace CultuurNet\UDB3\Symfony\Role;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\Services\ReadServiceInterface;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Role\Commands\RenameRole;
use CultuurNet\UDB3\Role\Commands\UpdateRoleRequestDeserializer;
use CultuurNet\UDB3\Role\Services\RoleEditingServiceInterface;
use CultuurNet\UDB3\Symfony\HttpFoundation\ApiProblemJsonResponse;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class EditRoleRestControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $roleId;

    /**
     * @var string
     */
    private $commandId;

    /**
     * @var string
     */
    private $labelId;

    /**
     * @var RoleEditingServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $editService;

    /**
     * @var CommandBusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commandBus;

    /**
     * @var UpdateRoleRequestDeserializer
     */
    private $updateRoleRequestDeserializer;

    /**
     * @var ReadServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $labelService;

    /**
     * @var EditRoleRestController
     */
    private $controller;

    public function setUp()
    {
        $this->roleId = (new UUID())->toNative();
        $this->commandId = (new UUID())->toNative();
        $this->labelId = (new UUID())->toNative();

        $this->editService = $this->getMock(RoleEditingServiceInterface::class);
        $this->commandBus = $this->getMock(CommandBusInterface::class);
        $this->updateRoleRequestDeserializer = $this->getMock(UpdateRoleRequestDeserializer::class);
        $this->labelService = $this->getMock(ReadServiceInterface::class);

        $this->controller = new EditRoleRestController(
            $this->editService,
            $this->commandBus,
            $this->updateRoleRequestDeserializer,
            $this->labelService
        );
    }

    /**
     * @test
     */
    public function it_creates_a_role()
    {
        $roleId = 'd01e0e24-4a8e-11e6-beb8-9e71128cae77';
        $roleName = new StringLiteral('roleName');

        $request = $this->makeRequest('POST', 'create_role.json');

        $this->editService->expects($this->once())
            ->method('create')
            ->with($roleName)
            ->willReturn($roleId);

        $response = $this->controller->create($request);

        $expectedJson = '{"roleId":"' . $roleId . '"}';

        $this->assertEquals($expectedJson, $response->getContent());
    }

    /**
     * @test
     */
    public function it_updates_a_role()
    {
        $roleId = 'd01e0e24-4a8e-11e6-beb8-9e71128cae77';
        $commandId = '456';
        $request = $this->makeRequest('PATCH', 'update_role.json');
        $request->headers->set('Content-Type', 'application/ld+json;domain-model=RenameRole');

        $renameRole = new RenameRole(
            new UUID($roleId),
            new StringLiteral('editRoleName')
        );

        $this->updateRoleRequestDeserializer->expects($this->once())
            ->method('deserialize')
            ->with($request, $roleId)
            ->willReturn($renameRole);

        $this->commandBus->expects($this->once())
            ->method('dispatch')
            ->with($renameRole)
            ->willReturn($commandId);

        $response = $this->controller->update($request, $roleId);

        $expectedJson = '{"commandId":"' . $commandId . '"}';

        $this->assertEquals($expectedJson, $response->getContent());
    }

    /**
     * @test
     */
    public function it_deletes_a_role()
    {
        $roleId = 'd01e0e24-4a8e-11e6-beb8-9e71128cae77';
        $commandId = '456';

        $this->editService->expects($this->once())
            ->method('delete')
            ->with($roleId)
            ->willReturn($commandId);

        $response = $this->controller->delete($roleId);

        $expectedJson = '{"commandId":"' . $commandId . '"}';

        $this->assertEquals($expectedJson, $response->getContent());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_no_roleId_is_given_to_delete()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Required field roleId is missing');
        $this->controller->delete('');
    }

    /**
     * @test
     */
    public function it_adds_a_label()
    {
        $this->editService->expects($this->once())
            ->method('addLabel')
            ->with(
                new UUID($this->roleId),
                new UUID($this->labelId)
            )
            ->willReturn($this->commandId);

        $response = $this->controller->addLabel($this->roleId, $this->labelId);

        $expectedJson = '{"commandId":"' . $this->commandId . '"}';

        $this->assertEquals($expectedJson, $response->getContent());
    }

    /**
     * @test
     */
    public function it_adds_a_label_by_name()
    {
        $labelName = 'foo';

        $label = new Entity(
            new UUID($this->labelId),
            new StringLiteral($labelName),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC()
        );

        $this->labelService->expects($this->once())
            ->method('getByName')
            ->with(new StringLiteral($labelName))
            ->willReturn($label);

        $this->editService->expects($this->once())
            ->method('addLabel')
            ->with(
                new UUID($this->roleId),
                new UUID($this->labelId)
            )
            ->willReturn($this->commandId);

        $response = $this->controller->addLabel($this->roleId, $labelName);

        $expectedJson = '{"commandId":"' . $this->commandId . '"}';

        $this->assertEquals($expectedJson, $response->getContent());
    }

    /**
     * @test
     */
    public function it_returns_an_error_response_when_adding_an_unknown_label()
    {
        $labelName = 'foo';

        $this->labelService->expects($this->once())
            ->method('getByName')
            ->with(new StringLiteral($labelName))
            ->willReturn(null);

        $response = $this->controller->addLabel($this->roleId, $labelName);

        $this->assertInstanceOf(ApiProblemJsonResponse::class, $response);
    }

    /**
     * @test
     */
    public function it_removes_a_label()
    {
        $this->editService->expects($this->once())
            ->method('removeLabel')
            ->with(
                new UUID($this->roleId),
                new UUID($this->labelId)
            )
            ->willReturn($this->commandId);

        $response = $this->controller->removeLabel($this->roleId, $this->labelId);

        $expectedJson = '{"commandId":"' . $this->commandId . '"}';

        $this->assertEquals($expectedJson, $response->getContent());
    }

    /**
     * @test
     */
    public function it_removes_a_label_by_name()
    {
        $labelName = 'foo';

        $label = new Entity(
            new UUID($this->labelId),
            new StringLiteral($labelName),
            Visibility::VISIBLE(),
            Privacy::PRIVACY_PUBLIC()
        );

        $this->labelService->expects($this->once())
            ->method('getByName')
            ->with(new StringLiteral($labelName))
            ->willReturn($label);

        $this->editService->expects($this->once())
            ->method('removeLabel')
            ->with(
                new UUID($this->roleId),
                new UUID($this->labelId)
            )
            ->willReturn($this->commandId);

        $response = $this->controller->removeLabel($this->roleId, $labelName);

        $expectedJson = '{"commandId":"' . $this->commandId . '"}';

        $this->assertEquals($expectedJson, $response->getContent());
    }

    /**
     * @test
     */
    public function it_returns_an_error_response_when_removing_an_unknown_label()
    {
        $labelName = 'foo';

        $this->labelService->expects($this->once())
            ->method('getByName')
            ->with(new StringLiteral($labelName))
            ->willReturn(null);

        $response = $this->controller->removeLabel($this->roleId, $labelName);

        $this->assertInstanceOf(ApiProblemJsonResponse::class, $response);
    }

    public function makeRequest($method, $file_name)
    {
        $content = $this->getJson($file_name);
        $request = new Request([], [], [], [], [], [], $content);
        $request->setMethod($method);

        return $request;
    }

    private function getJson($fileName)
    {
        $json = file_get_contents(
            __DIR__ . '/' . $fileName
        );

        return $json;
    }
}
