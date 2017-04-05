<?php

namespace CultuurNet\UDB3\Symfony;

use CultuurNet\UDB3\Media\MediaManagerInterface;
use CultuurNet\UDB3\Offer\AgeRange;
use CultuurNet\UDB3\Offer\OfferEditingServiceInterface;
use Symfony\Component\HttpFoundation\Request;

class OfferRestBaseControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * OfferEditingServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $offerEditingService;

    /**
     * @var MediaManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mediaManager;

    /**
     * @var OfferRestBaseController|\PHPUnit_Framework_MockObject_MockObject
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

        $this->offerEditingService->expects($this->once())
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
}
