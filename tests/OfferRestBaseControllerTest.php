<?php

namespace CultuurNet\UDB3\Symfony;

use CultuurNet\UDB3\Media\MediaManagerInterface;
use CultuurNet\UDB3\Offer\AgeRange;
use CultuurNet\UDB3\Offer\OfferEditingServiceInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OfferRestBaseControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OfferEditingServiceInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $offerEditingService;

    /**
     * @var MediaManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $mediaManager;

    /**
     * @var OfferRestBaseController|PHPUnit_Framework_MockObject_MockObject
     */
    private $offerRestBaseController;

    protected function setUp()
    {
        $this->offerEditingService = $this->createMock(
            OfferEditingServiceInterface::class
        );

        $this->mediaManager = $this->createMock(
            MediaManagerInterface::class
        );

        $this->offerRestBaseController = $this->getMockForAbstractClass(
            OfferRestBaseController::class,
            [
                $this->offerEditingService,
                $this->mediaManager,
            ]
        );
    }

    /**
     * @test
     */
    public function it_can_update_typical_age_range()
    {
        $cdbid = 'f636ae50-ac26-48f0-ac1f-929e361ae403';
        $content = '{"typicalAgeRange":"2-12"}';
        $request = new Request([], [], [], [], [], [], $content);

        $this->offerEditingService
            ->expects($this->once())
            ->method('updateTypicalAgeRange')
            ->with(
                $cdbid,
                AgeRange::fromString('2-12')
            );

        $this->offerRestBaseController->updateTypicalAgeRange(
            $request,
            $cdbid
        );
    }

    /**
     * @test
     */
    public function it_should_create_and_return_a_command_when_updating_an_offer_organization()
    {
        $this->offerEditingService
            ->expects($this->once())
            ->method('updateOrganizer')
            ->with(
                '301A7905-D329-49DD-8F2F-19CE6C3C10D4',
                '28AB9364-D650-4C6A-BCF5-E918A49025DF'
            )
            ->willReturn('8E6C0011-E4A8-4790-BD02-D6B4FF7980B9');

        $response = $this->offerRestBaseController->updateOrganizer(
            '301A7905-D329-49DD-8F2F-19CE6C3C10D4',
            '28AB9364-D650-4C6A-BCF5-E918A49025DF'
        );

        $expectedResponse = new JsonResponse(['commandId' => '8E6C0011-E4A8-4790-BD02-D6B4FF7980B9']);

        $this->assertEquals($expectedResponse, $response);
    }
}
