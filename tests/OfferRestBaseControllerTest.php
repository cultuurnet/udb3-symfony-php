<?php

namespace CultuurNet\UDB3\Symfony;

use CultuurNet\UDB3\BookingInfo;
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

    /**
     * @test
     */
    public function it_should_return_a_command_id_when_updating_booking_info()
    {
        $givenOfferId = 'b125e7b8-08ac-4740-80e1-b502ff716048';
        $givenJson = json_encode(
            [
                'bookingInfo' => [
                    'url' => 'https://publiq.be',
                    'urlLabel' => 'Publiq vzw',
                    'phone' => '044/444444',
                    'email' => 'info@publiq.be',
                    'availabilityStarts' => '2018-01-01T00:00:00+01:00',
                    'availabilityEnds' => '2018-01-31T23:59:59+01:00',
                ],
            ]
        );

        $givenRequest = new Request([], [], [], [], [], [], $givenJson);

        $expectedCommandId = '098a117c-a981-4737-a57b-b13d70ecb0f3';

        $this->offerEditingService->expects($this->once())
            ->method('updateBookingInfo')
            ->with(
                $givenOfferId,
                new BookingInfo(
                    'https://publiq.be',
                    'Publiq vzw',
                    '044/444444',
                    'info@publiq.be',
                    \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2018-01-01T00:00:00+01:00'),
                    \DateTimeImmutable::createFromFormat(\DATE_ATOM, '2018-01-31T23:59:59+01:00')
                )
            )
            ->willReturn($expectedCommandId);

        $expectedResponseBody = ['commandId' => $expectedCommandId];

        $actualResponse = $this->offerRestBaseController->updateBookingInfo($givenRequest, $givenOfferId);
        $actualResponseBody = $actualResponse->getContent();
        $actualResponseBody = json_decode($actualResponseBody, true);

        $this->assertEquals($expectedResponseBody, $actualResponseBody);
    }
}
