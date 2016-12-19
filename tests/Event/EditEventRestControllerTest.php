<?php

namespace CultuurNet\UDB3\Symfony\Event;

use CultureFeed_User;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Event\EventEditingServiceInterface;
use CultuurNet\UDB3\Event\EventServiceInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Media\MediaManagerInterface;
use CultuurNet\UDB3\Security\SecurityInterface;
use CultuurNet\UDB3\Title;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Geography\Country;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class EditEventRestControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EditEventRestController
     */
    private $controller;

    /**
     * @var EventServiceInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $entityService;

    /**
     * @var EventEditingServiceInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $eventEditor;

    /**
     * @var CultureFeed_User
     */
    private $user;

    /**
     * @var SecurityInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $security;

    /**
     * @var MediaManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $mediaManager;

    /**
     * @var IriGeneratorInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $iriGenerator;

    public function setUp()
    {
        $this->entityService = $this->getMock(EventServiceInterface::class);
        $this->eventEditor = $this->getMock(EventEditingServiceInterface::class);
        $this->user  = $this->getMock(CultureFeed_User::class);
        $this->mediaManager  = $this->getMock(MediaManagerInterface::class);
        $this->iriGenerator = $this->getMock(IriGeneratorInterface::class);
        $this->security  = $this->getMock(SecurityInterface::class);

        $this->controller = new EditEventRestController(
            $this->entityService,
            $this->eventEditor,
            $this->user,
            $this->mediaManager,
            $this->iriGenerator,
            $this->security
        );

        $this->iriGenerator
            ->expects($this->any())
            ->method('iri')
            ->willReturnCallback(
                function ($eventId) {
                    return 'http://du.de/event/' . $eventId;
                }
            );
    }

    /**
     * @test
     */
    public function it_creates_an_event()
    {
        $request = new Request([], [], [], [], [], [], $this->getMajorInfoJson());

        $this->eventEditor
            ->expects($this->once())
            ->method('createEvent')
            ->with(
                new Title('foo'),
                new EventType('1.8.2', 'PARTY!'),
                new Location(
                    'fe282e4f-35f5-480d-a90b-2720ab883b0a',
                    new StringLiteral('P-P-Partyzone'),
                    new Address(
                        new Street('acmelane 12'),
                        new PostalCode('3000'),
                        new Locality('Leuven'),
                        Country::fromNative('BE')
                    )
                )
            )
            ->willReturn('A14DD1C8-0F9C-4633-B56A-A908F009AD94');

        $response = $this->controller->createEvent($request);

        $expectedResponseContent = json_encode(
            [
                'eventId' => 'A14DD1C8-0F9C-4633-B56A-A908F009AD94',
                'url' => 'http://du.de/event/A14DD1C8-0F9C-4633-B56A-A908F009AD94',
            ]
        );

        $this->assertEquals($expectedResponseContent, $response->getContent());
    }

    /**
     * @test
     */
    public function it_should_update_major_info_with_all_the_provided_json_data()
    {
        $eventId = new UUID('7f71ebbd-b22b-4b94-96df-947ad0c1534f');
        $request = new Request([], [], [], [], [], [], $this->getMajorInfoJson());

        $this->eventEditor
            ->expects($this->once())
            ->method('updateMajorInfo')
            ->with(
                $eventId,
                new Title('foo'),
                new EventType('1.8.2', 'PARTY!'),
                new Location(
                    'fe282e4f-35f5-480d-a90b-2720ab883b0a',
                    new StringLiteral('P-P-Partyzone'),
                    new Address(
                        new Street('acmelane 12'),
                        new PostalCode('3000'),
                        new Locality('Leuven'),
                        Country::fromNative('BE')
                    )
                )
            )
            ->willReturn('A14DD1C8-0F9C-4633-B56A-A908F009AD94');

        $response = $this->controller->updateMajorInfo($request, $eventId->toNative());

        $expectedResponseContent = json_encode(
            ["commandId" => "A14DD1C8-0F9C-4633-B56A-A908F009AD94"]
        );

        $this->assertEquals($expectedResponseContent, $response->getContent());
    }

    /**
     * @return string
     */
    private function getMajorInfoJson()
    {
        return json_encode(
            [
                "name" => [
                    "nl" => "foo"
                ],
                "type" => [
                    "id" => "1.8.2",
                    "label" => "PARTY!"
                ],
                "theme" => [
                    "id" => "6.6.6",
                    "label" => "Pentagrams"
                ],
                "location" => [
                    "id" => "fe282e4f-35f5-480d-a90b-2720ab883b0a",
                    "name" => "P-P-Partyzone",
                    "address" => [
                        "streetAddress" => "acmelane 12",
                        "postalCode" => "3000",
                        "addressLocality" => "Leuven",
                        "addressCountry" => "BE",
                    ],
                ],
            ]
        );
    }
}
