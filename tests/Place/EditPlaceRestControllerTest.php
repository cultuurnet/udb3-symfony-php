<?php

namespace CultuurNet\UDB3\Symfony\Place;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\Relations\RepositoryInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Media\MediaManagerInterface;
use CultuurNet\UDB3\Place\PlaceEditingServiceInterface;
use CultuurNet\UDB3\Security\SecurityInterface;
use CultuurNet\UDB3\Title;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Geography\Country;
use ValueObjects\Identity\UUID;

class EditPlaceRestControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EditPlaceRestController
     */
    private $placeRestController;

    /**
     * @var PlaceEditingServiceInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $placeEditingService;

    /**
     * @var RepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $relationsRepository;

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
        $this->placeEditingService  = $this->getMock(PlaceEditingServiceInterface::class);
        $this->relationsRepository  = $this->getMock(RepositoryInterface::class);
        $this->security  = $this->getMock(SecurityInterface::class);
        $this->mediaManager  = $this->getMock(MediaManagerInterface::class);
        $this->iriGenerator = $this->getMock(IriGeneratorInterface::class);

        $this->placeRestController = new EditPlaceRestController(
            $this->placeEditingService,
            $this->relationsRepository,
            $this->security,
            $this->mediaManager,
            $this->iriGenerator
        );

        $this->iriGenerator
            ->expects($this->any())
            ->method('iri')
            ->willReturnCallback(
                function ($placeId) {
                    return 'http://du.de/place/' . $placeId;
                }
            );
    }

    /**
     * @test
     */
    public function it_should_respond_with_the_location_of_the_new_offer_when_creating_a_place()
    {
        $request = new Request([], [], [], [], [], [], $this->getMajorInfoJson());

        $this->placeEditingService
            ->expects($this->once())
            ->method('createPlace')
            ->with(
                new Title('foo'),
                new EventType('1.8.2', 'PARTY!'),
                new Address(
                    new Street('acmelane 12'),
                    new PostalCode('3000'),
                    new Locality('Leuven'),
                    Country::fromNative('BE')
                )
            )
            ->willReturn('A14DD1C8-0F9C-4633-B56A-A908F009AD94');

        $response = $this->placeRestController->createPlace($request);

        $expectedResponseContent = json_encode(
            [
                "placeId" => "A14DD1C8-0F9C-4633-B56A-A908F009AD94",
                "url" => "http://du.de/place/A14DD1C8-0F9C-4633-B56A-A908F009AD94"
            ]
        );

        $this->assertEquals($response->getContent(), $expectedResponseContent);
    }

    /**
     * @test
     */
    public function it_updates_major_info()
    {
        $placeId = new UUID('A14DD1C8-0F9C-4633-B56A-A908F009AD94');
        $request = new Request([], [], [], [], [], [], $this->getMajorInfoJson());
        $commandId = 'fc6b8c00-7362-4e69-826d-9a2fc9cc8a2e';

        $this->placeEditingService
            ->expects($this->once())
            ->method('updateMajorInfo')
            ->with(
                $placeId,
                new Title('foo'),
                new EventType('1.8.2', 'PARTY!'),
                new Address(
                    new Street('acmelane 12'),
                    new PostalCode('3000'),
                    new Locality('Leuven'),
                    Country::fromNative('BE')
                )
            )
            ->willReturn('fc6b8c00-7362-4e69-826d-9a2fc9cc8a2e');

        $response = $this->placeRestController->updateMajorInfo($request, $placeId->toNative());

        $expectedResponseContent = json_encode(["commandId" => $commandId]);

        $this->assertEquals($response->getContent(), $expectedResponseContent);
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
                "address" => [
                    "streetAddress" => "acmelane 12",
                    "postalCode" => "3000",
                    "addressLocality" => "Leuven",
                    "addressCountry" => "BE",
                ],
            ]
        );
    }
}
