<?php


namespace CultuurNet\UDB3\Symfony\Offer;

use Broadway\CommandHandling\CommandBusInterface;
use CultuurNet\UDB3\Event\Commands\Moderation\Approve;
use CultuurNet\UDB3\Event\Commands\Moderation\FlagAsDuplicate;
use CultuurNet\UDB3\Event\Commands\Moderation\FlagAsInappropriate;
use CultuurNet\UDB3\Event\Commands\Moderation\Reject;
use CultuurNet\UDB3\Place\Commands\Moderation\Approve as ApprovePlace;
use CultuurNet\UDB3\Place\Commands\Moderation\FlagAsDuplicate as FlagAsDuplicatePlace;
use CultuurNet\UDB3\Place\Commands\Moderation\FlagAsInappropriate as FlagAsInappropriatePlace;
use CultuurNet\UDB3\Place\Commands\Moderation\Reject as RejectPlace;
use CultuurNet\UDB3\Offer\Commands\AbstractCommand;
use CultuurNet\UDB3\Offer\OfferType;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String as StringLiteral;

class PatchOfferRestControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CommandBusInterface | PHPUnit_Framework_MockObject_MockObject
     */
    private $commandBus;

    /**
     * @var string
     */
    private $itemId = 'e6238239-4ec1-4778-a0ca-bf7fb0256eed';

    public function setUp()
    {
        $this->commandBus = $this->getMock(CommandBusInterface::class);
    }

    /**
     * @test
     * @dataProvider commandRequestDataProvider
     * @param OfferType $offerType
     * @param Request $request
     * @param AbstractCommand $expectedCommand
     */
    public function it_should_dispatch_the_requested_offer_commands(
        OfferType $offerType,
        Request $request,
        AbstractCommand $expectedCommand
    ) {
        $controller = new PatchOfferRestController($offerType, $this->commandBus);

        $expectedResponse = new JsonResponse([
            'commandId' =>  '6a9762dc-f0d6-400d-b097-00ada39a76e2'
        ]);

        $this->commandBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($expectedCommand)
            ->willReturn('6a9762dc-f0d6-400d-b097-00ada39a76e2');

        $response = $controller->handle($request, $this->itemId);

        $this->assertEquals($expectedResponse->getContent(), $response->getContent());
    }

    public function commandRequestDataProvider()
    {
        return [
            'Approve event' => [
                'offerType' => OfferType::EVENT(),
                'request' => $this->generatePatchRequest('application/ld+json;domain-model=Approve'),
                'expectedCommand' => new Approve($this->itemId)
            ],
            'Reject event' => [
                'offerType' => OfferType::EVENT(),
                'request' => $this->generatePatchRequest(
                    'application/ld+json;domain-model=Reject',
                    json_encode(['reason' => 'Description missing :('])
                ),
                'expectedCommand' => new Reject($this->itemId, new StringLiteral('Description missing :('))
            ],
            'Flag event as duplicate' => [
                'offerType' => OfferType::EVENT(),
                'request' => $this->generatePatchRequest('application/ld+json;domain-model=FlagAsDuplicate'),
                'expectedCommand' => new FlagAsDuplicate($this->itemId)
            ],
            'Flag event as inappropriate' => [
                'offerType' => OfferType::EVENT(),
                'request' => $this->generatePatchRequest('application/ld+json;domain-model=FlagAsInappropriate'),
                'expectedCommand' => new FlagAsInappropriate($this->itemId)
            ],
            'Approve place' => [
                'offerType' => OfferType::PLACE(),
                'request' => $this->generatePatchRequest('application/ld+json;domain-model=Approve'),
                'expectedCommand' => new ApprovePlace($this->itemId)
            ],
            'Reject place' => [
                'offerType' => OfferType::PLACE(),
                'request' => $this->generatePatchRequest(
                    'application/ld+json;domain-model=Reject',
                    json_encode(['reason' => 'Description missing :('])
                ),
                'expectedCommand' => new RejectPlace($this->itemId, new StringLiteral('Description missing :('))
            ],
            'Flag place as duplicate' => [
                'offerType' => OfferType::PLACE(),
                'request' => $this->generatePatchRequest('application/ld+json;domain-model=FlagAsDuplicate'),
                'expectedCommand' => new FlagAsDuplicatePlace($this->itemId)
            ],
            'Flag place as inappropriate' => [
                'offerType' => OfferType::PLACE(),
                'request' => $this->generatePatchRequest('application/ld+json;domain-model=FlagAsInappropriate'),
                'expectedCommand' => new FlagAsInappropriatePlace($this->itemId)
            ],
        ];
    }

    /**
     * @param string $contentType
     * @param $content
     * @return Request
     */
    private function generatePatchRequest($contentType, $content = null)
    {
        $request = Request::create('/offer/' . $this->itemId, 'PATCH', [], [], [], [], $content);
        $request->headers->set('Content-Type', $contentType);

        return $request;
    }
}
