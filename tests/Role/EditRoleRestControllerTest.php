<?php

namespace CultuurNet\UDB3\Symfony\Role;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\Role\Commands\RenameRole;
use CultuurNet\UDB3\Role\Commands\UpdateRoleRequestDeserializer;
use CultuurNet\UDB3\Role\Services\RoleEditingServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class EditRoleRestControllerTest extends \PHPUnit_Framework_TestCase
{
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
     * @var EditRoleRestController
     */
    private $controller;

    public function setUp()
    {
        $this->editService = $this->getMock(RoleEditingServiceInterface::class);
        $this->commandBus = $this->getMock(CommandBusInterface::class);
        $this->updateRoleRequestDeserializer = $this->getMock(UpdateRoleRequestDeserializer::class);
        $this->controller = new EditRoleRestController(
            $this->editService,
            $this->commandBus,
            $this->updateRoleRequestDeserializer
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