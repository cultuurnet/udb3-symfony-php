<?php

namespace CultuurNet\UDB3\Symfony\Place;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use CultureFeed_User;
use CultuurNet\UDB3\EntityServiceInterface;
use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Media\MediaManagerInterface;
use CultuurNet\UDB3\Offer\SecurityInterface;
use CultuurNet\UDB3\Place\PlaceEditingServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use CultuurNet\UDB3\Event\ReadModel\Relations\RepositoryInterface;

class EditPlaceRestControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EditPlaceRestController
     */
    private $placeRestController;

    /**
     * @var EntityServiceInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $entityService;

    /**
     * @var PlaceEditingServiceInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $placeEditingService;

    /**
     * @var RepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $relationsRepository;

    /**
     * @var CultureFeed_User|PHPUnit_Framework_MockObject_MockObject
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
        $this->entityService = $this->getMock(EntityServiceInterface::class);
        $this->placeEditingService  = $this->getMock(PlaceEditingServiceInterface::class);
        $this->relationsRepository  = $this->getMock(RepositoryInterface::class);
        $this->user  = $this->getMock(CultureFeed_User::class);
        $this->security  = $this->getMock(SecurityInterface::class);
        $this->mediaManager  = $this->getMock(MediaManagerInterface::class);
        $this->iriGenerator = $this->getMock(IriGeneratorInterface::class);

        $this->placeRestController = new EditPlaceRestController(
            $this->entityService,
            $this->placeEditingService,
            $this->relationsRepository,
            $this->user,
            $this->security,
            $this->mediaManager,
            $this->iriGenerator
        );
    }

    /**
     * @test
     */
    public function it_should_respond_with_the_location_of_the_new_offer_when_creating_a_place()
    {
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
            "address" => [
                "streetAddress" => "acmelane 12",
                "postalCode" => "3000",
                "addressLocality" => "Leuven",
                "addressCountry" => "BE",
            ]
        ]);

        $request = new Request([], [], [], [], [], [], $content);
        
        $this->placeEditingService
            ->method('createPlace')
            ->willReturn('A14DD1C8-0F9C-4633-B56A-A908F009AD94');
        
        $this->iriGenerator
            ->method('iri')
            ->with('A14DD1C8-0F9C-4633-B56A-A908F009AD94')
            ->willReturn('http://du.de/place/A14DD1C8-0F9C-4633-B56A-A908F009AD94');

        $response = $this->placeRestController->createPlace($request);

        $expectedResponseContent = \GuzzleHttp\json_encode([
            "placeId" => "A14DD1C8-0F9C-4633-B56A-A908F009AD94",
            "url" => "http://du.de/place/A14DD1C8-0F9C-4633-B56A-A908F009AD94"
        ]);
        
        $this->assertEquals($response->getContent(), $expectedResponseContent);
    }
}
