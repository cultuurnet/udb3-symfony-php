<?php

namespace CultuurNet\UDB3\Symfony\Place;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Event\ReadModel\Relations\RepositoryInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Media\MediaManagerInterface;
use CultuurNet\UDB3\Place\PlaceEditingServiceInterface;
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
     * @var MediaManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $mediaManager;

    /**
     * @var IriGeneratorInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $iriGenerator;

    public function setUp()
    {
        $this->placeEditingService  = $this->createMock(PlaceEditingServiceInterface::class);
        $this->relationsRepository  = $this->createMock(RepositoryInterface::class);
        $this->mediaManager  = $this->createMock(MediaManagerInterface::class);
        $this->iriGenerator = $this->createMock(IriGeneratorInterface::class);

        $this->placeRestController = new EditPlaceRestController(
            $this->placeEditingService,
            $this->relationsRepository,
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

        $this->assertEquals($expectedResponseContent, $response->getContent());
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

        $this->assertEquals($expectedResponseContent, $response->getContent());
    }

    /**
     * @test
     */
    public function it_should_update_the_address_of_a_place_for_a_given_language()
    {
        $json = json_encode(
            [
                'streetAddress' => 'Eenmeilaan 35',
                'postalCode' => '3010',
                'addressLocality' => 'Kessel-Lo',
                'addressCountry' => 'BE',
            ]
        );

        $request = new Request([], [], [], [], [], [], $json);

        $placeId = '6645274f-d969-4d70-865e-3ec799db9624';
        $lang = 'nl';

        $expectedCommandId = 'b17dd484-dbf6-4b77-a00c-90cf919f929b';

        $this->placeEditingService->expects($this->once())
            ->method('updateAddress')
            ->with(
                $placeId,
                new Address(
                    new Street('Eenmeilaan 35'),
                    new PostalCode('3010'),
                    new Locality('Kessel-Lo'),
                    Country::fromNative('BE')
                ),
                new Language($lang)
            )
            ->willReturn($expectedCommandId);

        $expectedResponseContent = json_encode(['commandId' => $expectedCommandId]);

        $response = $this->placeRestController->updateAddress($request, $placeId, $lang);
        $actualResponseContent = $response->getContent();

        $this->assertEquals($expectedResponseContent, $actualResponseContent);
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
