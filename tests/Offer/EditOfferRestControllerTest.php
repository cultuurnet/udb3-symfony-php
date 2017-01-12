<?php

namespace CultuurNet\UDB3\Symfony\Offer;

use CultuurNet\UDB3\DescriptionJSONDeserializer;
use CultuurNet\UDB3\LabelJSONDeserializer;
use CultuurNet\UDB3\Offer\OfferEditingServiceInterface;
use CultuurNet\UDB3\PriceInfo\BasePrice;
use CultuurNet\UDB3\PriceInfo\Price;
use CultuurNet\UDB3\PriceInfo\PriceInfo;
use CultuurNet\UDB3\PriceInfo\Tariff;
use CultuurNet\UDB3\Symfony\Deserializer\PriceInfo\PriceInfoJSONDeserializer;
use CultuurNet\UDB3\TitleJSONDeserializer;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Money\Currency;
use ValueObjects\StringLiteral\StringLiteral;

class EditOfferRestControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OfferEditingServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $editService;

    /**
     * @var LabelJSONDeserializer
     */
    private $labelDeserializer;

    /**
     * @var TitleJSONDeserializer
     */
    private $titleDeserializer;

    /**
     * @var DescriptionJSONDeserializer
     */
    private $descriptionDeserializer;

    /**
     * @var PriceInfoJSONDeserializer
     */
    private $priceInfoDeserializer;

    /**
     * @var EditOfferRestController
     */
    private $controller;

    public function setUp()
    {
        $this->editService = $this->createMock(OfferEditingServiceInterface::class);

        $this->labelDeserializer = new LabelJSONDeserializer();
        $this->titleDeserializer = new TitleJSONDeserializer();
        $this->descriptionDeserializer = new DescriptionJSONDeserializer();
        $this->priceInfoDeserializer = new PriceInfoJSONDeserializer();

        $this->controller = new EditOfferRestController(
            $this->editService,
            $this->labelDeserializer,
            $this->titleDeserializer,
            $this->descriptionDeserializer,
            $this->priceInfoDeserializer
        );
    }

    /**
     * @test
     */
    public function it_updates_price_info()
    {
        $data = '[
            {"category": "base", "price": 15, "priceCurrency": "EUR"},
            {"category": "tarrif", "name": "Werkloze dodo kwekers", "price": 0, "priceCurrency": "EUR"}
        ]';

        $cdbid = 'c6ff4c27-bdbb-452f-a1b5-d9e2e3aa846c';

        $request = new Request([], [], [], [], [], [], $data);

        $expectedBasePrice = new BasePrice(
            new Price(1500),
            Currency::fromNative('EUR')
        );

        $expectedTariff = new Tariff(
            new StringLiteral('Werkloze dodo kwekers'),
            new Price(0),
            Currency::fromNative('EUR')
        );

        $expectedPriceInfo = (new PriceInfo($expectedBasePrice))
            ->withExtraTariff($expectedTariff);

        $commandId = 'f9989e43-d14d-4a28-9092-34d7cd1a71fd';

        $this->editService->expects($this->once())
            ->method('updatePriceInfo')
            ->with(
                $cdbid,
                $expectedPriceInfo
            )
            ->willReturn($commandId);

        $expectedResponseContent = '{"commandId":"' . $commandId . '"}';

        $actualResponseContent = $this->controller
            ->updatePriceInfo($request, $cdbid)
            ->getContent();

        $this->assertEquals($expectedResponseContent, $actualResponseContent);
    }
}
