<?php

namespace CultuurNet\UDB3\Symfony\Event;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Event\EventEditingServiceInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ValueObjects\Audience;
use CultuurNet\UDB3\Event\ValueObjects\AudienceType;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Location\LocationId;
use CultuurNet\UDB3\Media\MediaManagerInterface;
use CultuurNet\UDB3\Title;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Geography\Country;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

class EditEventRestControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EditEventRestController
     */
    private $controller;

    /**
     * @var EventEditingServiceInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $eventEditor;

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
        $this->eventEditor = $this->createMock(EventEditingServiceInterface::class);
        $this->mediaManager  = $this->createMock(MediaManagerInterface::class);
        $this->iriGenerator = $this->createMock(IriGeneratorInterface::class);

        $this->controller = new EditEventRestController(
            $this->eventEditor,
            $this->mediaManager,
            $this->iriGenerator
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
                new Language('nl'),
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
    public function it_copies_an_event()
    {
        $calendarData = json_encode([
            'calenderType' => 'permanent'
        ]);

        $request = new Request([], [], [], [], [], [], $calendarData);

        $this->eventEditor
            ->expects($this->once())
            ->method('copyEvent')
            ->with(
                '1539b109-5eec-43ef-8dc9-830cbe0cff8e',
                new Calendar(CalendarType::PERMANENT())
            )
            ->willReturn('A14DD1C8-0F9C-4633-B56A-A908F009AD94');

        $response = $this->controller->copyEvent($request, '1539b109-5eec-43ef-8dc9-830cbe0cff8e');

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
     * @test
     */
    public function it_updates_location()
    {
        $eventId = '7f71ebbd-b22b-4b94-96df-947ad0c1534f';
        $locationId = '9a1fe7fc-4129-4563-aafd-414ef25b2814';
        $commandId = 'commandId';

        $this->eventEditor->expects($this->once())
            ->method('updateLocation')
            ->with(
                $eventId,
                new LocationId('9a1fe7fc-4129-4563-aafd-414ef25b2814')
            )
            ->willReturn($commandId);

        $response = $this->controller->updateLocation($eventId, $locationId);

        $expectedResponse = json_encode(['commandId' => $commandId]);
        $this->assertEquals($expectedResponse, $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_updates_audience()
    {
        $eventId = new UUID('7f71ebbd-b22b-4b94-96df-947ad0c1534f');
        $commandId = 'commandId';
        $content = json_encode(['audienceType' => 'education']);
        $request = new Request([], [], [], [], [], [], $content);

        $this->eventEditor->expects($this->once())
            ->method('updateAudience')
            ->with(
                $eventId,
                new Audience(AudienceType::EDUCATION())
            )
            ->willReturn($commandId);

        $response = $this->controller->updateAudience($request, $eventId);

        $expectedResponse = json_encode(['commandId' => $commandId]);
        $this->assertEquals($expectedResponse, $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_error_when_updating_audience_but_missing_cdbid()
    {
        $eventId = null;
        $content = json_encode(['audienceType' => 'education']);
        $request = new Request([], [], [], [], [], [], $content);

        $response = $this->controller->updateAudience($request, $eventId);

        $expectedResponse = json_encode(['error' => 'cdbid is required.']);
        $this->assertEquals($expectedResponse, $response->getContent());
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_error_when_updating_audience_but_missing_audience_type()
    {
        $eventId = new UUID('7f71ebbd-b22b-4b94-96df-947ad0c1534f');
        $request = new Request();

        $response = $this->controller->updateAudience($request, $eventId);

        $expectedResponse = json_encode(['error' => 'audience type is required.']);
        $this->assertEquals($expectedResponse, $response->getContent());
        $this->assertEquals(400, $response->getStatusCode());
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
