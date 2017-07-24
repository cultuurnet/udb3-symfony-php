<?php

namespace CultuurNet\UDB3\Symfony\Offer;

use CultuurNet\UDB3\DescriptionJSONDeserializer;
use CultuurNet\UDB3\Label\Label;
use CultuurNet\UDB3\LabelJSONDeserializer;
use CultuurNet\UDB3\Offer\OfferEditingServiceInterface;
use CultuurNet\UDB3\PriceInfo\BasePrice;
use CultuurNet\UDB3\PriceInfo\Price;
use CultuurNet\UDB3\PriceInfo\PriceInfo;
use CultuurNet\UDB3\PriceInfo\Tariff;
use CultuurNet\UDB3\Symfony\Deserializer\PriceInfo\PriceInfoJSONDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\TitleJSONDeserializer;
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
    public function it_adds_a_label()
    {
        $label = 'test label';
        $cdbid = 'c6ff4c27-bdbb-452f-a1b5-d9e2e3aa846c';

        $commandId = 'f9989e43-d14d-4a28-9092-34d7cd1a71fd';

        $this->editService->expects($this->once())
            ->method('addLabel')
            ->with(
                $cdbid,
                $label
            )
            ->willReturn($commandId);

        $expectedResponseContent = '{"commandId":"' . $commandId . '"}';

        $actualResponseContent = $this->controller
            ->addLabel($cdbid, $label)
            ->getContent();

        $this->assertEquals($expectedResponseContent, $actualResponseContent);
    }

    /**
     * @test
     */
    public function it_adds_label_through_json()
    {
        $data = '{
            "label": "Bio",
            "offers": [
                {
                    "@id": "http://culudb-silex.dev:8080/event/0823f57e-a6bd-450a-b4f5-8459b4b11043",
                    "@type": "Event"
                }
            ]
        }';

        $cdbid = 'c6ff4c27-bdbb-452f-a1b5-d9e2e3aa846c';

        $request = new Request([], [], [], [], [], [], $data);

        $json = new StringLiteral($data);
        $expectedLabel = $this->labelDeserializer->deserialize($json);

        $commandId = 'f9989e43-d14d-4a28-9092-34d7cd1a71fd';

        $this->editService->expects($this->once())
            ->method('addLabel')
            ->with(
                $cdbid,
                $expectedLabel
            )
            ->willReturn($commandId);

        $expectedResponseContent = '{"commandId":"' . $commandId . '"}';

        $actualResponseContent = $this->controller
            ->addLabelFromJsonBody($request, $cdbid)
            ->getContent();

        $this->assertEquals($expectedResponseContent, $actualResponseContent);
    }

    /**
     * @test
     */
    public function it_removes_a_label()
    {
        $label = 'test label';
        $cdbid = 'c6ff4c27-bdbb-452f-a1b5-d9e2e3aa846c';

        $commandId = 'f9989e43-d14d-4a28-9092-34d7cd1a71fd';

        $this->editService->expects($this->once())
            ->method('removeLabel')
            ->with(
                $cdbid,
                $label
            )
            ->willReturn($commandId);

        $expectedResponseContent = '{"commandId":"' . $commandId . '"}';

        $actualResponseContent = $this->controller
            ->removeLabel($cdbid, $label)
            ->getContent();

        $this->assertEquals($expectedResponseContent, $actualResponseContent);
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
