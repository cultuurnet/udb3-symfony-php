<?php

namespace CultuurNet\UDB3\Symfony\Event;

use CultureFeed_User;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Event\EventEditingServiceInterface;
use CultuurNet\UDB3\Event\EventServiceInterface;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Location;
use CultuurNet\UDB3\Media\MediaManagerInterface;
use CultuurNet\UDB3\Offer\SecurityInterface;
use CultuurNet\UDB3\Symfony\User\UserLabelMemoryRestController;
use CultuurNet\UDB3\Title;
use CultuurNet\UDB3\UsedLabelsMemory\UsedLabelsMemoryServiceInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Geography\Country;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;
use Zend\Validator\File\Count;

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
     * @var UsedLabelsMemoryServiceInterface
     */
    private $usedLabelsMemory;

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
        $this->usedLabelsMemory = $this->getMock(UsedLabelsMemoryServiceInterface::class);
        $this->user  = $this->getMock(CultureFeed_User::class);
        $this->mediaManager  = $this->getMock(MediaManagerInterface::class);
        $this->iriGenerator = $this->getMock(IriGeneratorInterface::class);
        $this->security  = $this->getMock(SecurityInterface::class);

        $this->controller = new EditEventRestController(
            $this->entityService,
            $this->eventEditor,
            $this->usedLabelsMemory,
            $this->user,
            $this->mediaManager,
            $this->iriGenerator,
            $this->security
        );
    }

    /**
     * @test
     */
    public function it_should_update_major_info_with_all_the_provided_json_data()
    {
        $eventId = UUID::generateAsString();
        $content = \GuzzleHttp\json_encode([
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
        ]);

        $request = new Request([], [], [], [], [], [], $content);

        $this->eventEditor
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

        $this->iriGenerator
            ->method('iri')
            ->with($eventId)
            ->willReturn('http://du.de/event/' . $eventId);

        $response = $this->controller->updateMajorInfo($request, $eventId);

        $expectedResponseContent = \GuzzleHttp\json_encode([
            "commandId" => "A14DD1C8-0F9C-4633-B56A-A908F009AD94",
        ]);

        $this->assertEquals($expectedResponseContent, $response->getContent());
    }
}

