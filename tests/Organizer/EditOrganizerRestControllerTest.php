<?php

namespace CultuurNet\UDB3\Symfony\Organizer;

use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Organizer\OrganizerEditingServiceInterface;

class EditOrganizerRestControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrganizerEditingServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $editService;

    /**
     * @var IriGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $iriGenerator;

    /**
     * @var EditOrganizerRestController
     */
    private $controller;

    public function setUp()
    {
        $this->editService = $this->getMock(OrganizerEditingServiceInterface::class);
        $this->iriGenerator = $this->getMock(IriGeneratorInterface::class);
        $this->controller = new EditOrganizerRestController($this->editService, $this->iriGenerator);
    }

    /**
     * @test
     */
    public function it_deletes_an_organizer()
    {
        $cdbId = '123';
        $commandId = '456';

        $this->editService->expects($this->once())
            ->method('delete')
            ->with($cdbId)
            ->willReturn($commandId);

        $response = $this->controller->delete($cdbId);

        $expectedJson = '{"commandId":"' . $commandId . '"}';

        $this->assertEquals($expectedJson, $response->getContent());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_no_cdbid_is_given_to_delete()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Required field cdbid is missing');
        $this->controller->delete('');
    }
}
