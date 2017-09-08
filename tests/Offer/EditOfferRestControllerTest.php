<?php

namespace CultuurNet\UDB3\Symfony\Offer;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Description;
use CultuurNet\UDB3\DescriptionJSONDeserializer;
use CultuurNet\UDB3\LabelJSONDeserializer;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\OfferEditingServiceInterface;
use CultuurNet\UDB3\PriceInfo\BasePrice;
use CultuurNet\UDB3\PriceInfo\Price;
use CultuurNet\UDB3\PriceInfo\PriceInfo;
use CultuurNet\UDB3\PriceInfo\Tariff;
use CultuurNet\UDB3\Symfony\Deserializer\Calendar\CalendarJSONParser;
use CultuurNet\UDB3\Symfony\Deserializer\CalendarDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\DataValidator\DataValidatorInterface;
use CultuurNet\UDB3\Symfony\Deserializer\PriceInfo\PriceInfoJSONDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\TitleJSONDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\Calendar\CalendarJSONDeserializer;
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
     * @var CalendarDeserializer
     */
    private $calendarDeserializer;

    /**
     * @var DataValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $calendarDataValidator;

    /**
     * @var EditOfferRestController
     */
    private $controller;

    public function setUp()
    {
        $this->editService = $this->createMock(OfferEditingServiceInterface::class);

        $this->calendarDataValidator = $this->createMock(DataValidatorInterface::class);

        $this->labelDeserializer = new LabelJSONDeserializer();
        $this->titleDeserializer = new TitleJSONDeserializer();
        $this->descriptionDeserializer = new DescriptionJSONDeserializer();
        $this->priceInfoDeserializer = new PriceInfoJSONDeserializer();
        $this->calendarDeserializer = new CalendarJSONDeserializer(
            new CalendarJSONParser(),
            $this->calendarDataValidator
        );

        $this->controller = new EditOfferRestController(
            $this->editService,
            $this->labelDeserializer,
            $this->titleDeserializer,
            $this->descriptionDeserializer,
            $this->priceInfoDeserializer,
            $this->calendarDeserializer
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

    /**
     * @test
     */
    public function it_should_use_the_editing_service_and_retun_the_command_id_when_updating_an_offer_description_by_language()
    {
        $descriptionData = '{"description": "nieuwe beschrijving"}';

        $request = new Request([], [], [], [], [], [], $descriptionData);

        $this->editService->expects($this->once())
            ->method('updateDescription')
            ->with(
                'EC545F35-C76E-4EFC-8AB0-5024DA866CA0',
                new Language('nl'),
                new Description('nieuwe beschrijving')
            )
            ->willReturn('3390051C-3071-4917-896D-AA0B792392C0');

        $responseContent = $this->controller
            ->updateDescription($request, 'EC545F35-C76E-4EFC-8AB0-5024DA866CA0', 'nl')
            ->getContent();

        $expectedResponseContent = '{"commandId":"3390051C-3071-4917-896D-AA0B792392C0"}';

        $this->assertEquals($expectedResponseContent, $responseContent);
    }

    /**
     * @test
     */
    public function it_handles_updating_calendar()
    {
        $eventId = '0f4ea9ad-3681-4f3b-adc2-4b8b00dd845a';

        $calendar = new Calendar(
            CalendarType::PERMANENT()
        );

        $calendarData = '{"calendarType": "permanent"}';

        $request = new Request([], [], [], [], [], [], $calendarData);

        $this->editService->expects($this->once())
            ->method('updateCalendar')
            ->with(
                $eventId,
                $calendar
            )
            ->willReturn('commandId');

        $responseContent = $this->controller
            ->updateCalendar($request, $eventId)
            ->getContent();

        $expectedResponseContent = '{"commandId":"commandId"}';

        $this->assertEquals($expectedResponseContent, $responseContent);
    }

    /**
     * @test
     */
    public function it_should_return_a_command_id_when_updating_theme_by_id()
    {
        $this->editService
            ->expects($this->once())
            ->method('updateTheme')
            ->with(
                'B904CD9E-0125-473E-ADDB-EC5E7ED12875',
                new StringLiteral('CEFFE9F0-AD3C-446B-838A-0E309843C5E1')
            )
            ->willReturn('EBFF0B3A-0401-4C4D-A355-D326C8A4F31A');

        $responseContent = $this->controller
            ->updateTheme(
                'B904CD9E-0125-473E-ADDB-EC5E7ED12875',
                'CEFFE9F0-AD3C-446B-838A-0E309843C5E1'
            )
            ->getContent();

        $expectedResponseContent = '{"commandId":"EBFF0B3A-0401-4C4D-A355-D326C8A4F31A"}';
        $this->assertEquals($expectedResponseContent, $responseContent);
    }

    /**
     * @test
     */
    public function it_should_return_a_command_id_when_updating_type_by_id()
    {
        $this->editService
            ->expects($this->once())
            ->method('updateType')
            ->with(
                'BA403978-7378-41F7-A416-C5D2155D6EDE',
                new StringLiteral('6B22AC5E-83AF-4590-91C9-91B4D66426CD')
            )
            ->willReturn('2B7D4F57-A813-4C4F-8B32-EA7091A0FF1B');

        $responseContent = $this->controller
            ->updateType(
                'BA403978-7378-41F7-A416-C5D2155D6EDE',
                '6B22AC5E-83AF-4590-91C9-91B4D66426CD'
            )
            ->getContent();

        $expectedResponseContent = '{"commandId":"2B7D4F57-A813-4C4F-8B32-EA7091A0FF1B"}';
        $this->assertEquals($expectedResponseContent, $responseContent);
    }
}
