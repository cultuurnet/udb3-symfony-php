<?php

namespace CultuurNet\UDB3\Symfony\Organizer;

use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Organizer\OrganizerEditingServiceInterface;
use Symfony\Component\HttpFoundation\Request;

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

    public function it_creates_an_organizer()
    {
        $cdbId = '123';
        $commandId = '456';

        $this->editService->expects($this->once())
            ->method('create')
            ->with($cdbId)
            ->willReturn($commandId);

        $request = $this->makeRequest('POST', 'organizer_create.json');

        $response = $this->controller->create($request);

        $expectedJson = '{"commandId":"' . $commandId . '"}';

        $this->assertEquals($expectedJson, $response->getContent());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_no_title_is_given_on_create()
    {
        $request = $this->makeRequest('POST', 'organizer_create_without_name.json');

        $response =  $this->controller->create($request);
        $expectedJson = '{"error":"Required fields are missing"}';

        $this->assertEquals($expectedJson, $response->getContent());

        $responseStatusCode = $response->getStatusCode();
        $expectedResponseStatusCode = '400';

        $this->assertEquals($expectedResponseStatusCode, $responseStatusCode);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_no_website_is_given_on_create()
    {
        $request = $this->makeRequest('POST', 'organizer_create_without_website.json');

        $response =  $this->controller->create($request);
        $expectedJson = '{"error":"Required fields are missing"}';

        $this->assertEquals($expectedJson, $response->getContent());

        $responseStatusCode = $response->getStatusCode();
        $expectedResponseStatusCode = '400';

        $this->assertEquals($expectedResponseStatusCode, $responseStatusCode);
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
            __DIR__ . '/samples/' . $fileName
        );

        return $json;
    }
}
