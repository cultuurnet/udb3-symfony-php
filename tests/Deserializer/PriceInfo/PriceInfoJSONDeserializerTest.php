<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\PriceInfo;

use CultuurNet\Deserializer\MissingValueException;
use CultuurNet\UDB3\PriceInfo\BasePrice;
use CultuurNet\UDB3\PriceInfo\Price;
use CultuurNet\UDB3\PriceInfo\PriceInfo;
use CultuurNet\UDB3\PriceInfo\Tariff;
use ValueObjects\Money\Currency;
use ValueObjects\StringLiteral\StringLiteral;

class PriceInfoJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PriceInfoJSONDeserializer
     */
    private $deserializer;

    public function setUp()
    {
        $this->deserializer = new PriceInfoJSONDeserializer();
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_a_category_is_missing()
    {
        $data = new StringLiteral(
            '[{"name": "Senioren", "price": 10.5, "priceCurrency": "EUR"}]'
        );

        $this->setExpectedException(
            MissingValueException::class,
            'The category property is required for each priceInfo item.'
        );

        $this->deserializer->deserialize($data);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_a_tariff_is_missing_a_name()
    {
        $data = new StringLiteral(
            '[{"category": "tariff", "price": 10.5, "priceCurrency": "EUR"}]'
        );

        $this->setExpectedException(
            MissingValueException::class,
            'The name property is required for each tariff.'
        );

        $this->deserializer->deserialize($data);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_a_price_is_missing()
    {
        $data = new StringLiteral(
            '[{"name": "Senioren", "category": "tariff", "priceCurrency": "EUR"}]'
        );

        $this->setExpectedException(
            MissingValueException::class,
            'The price property is required for each priceInfo item.'
        );

        $this->deserializer->deserialize($data);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_more_than_one_base_price_is_found()
    {
        $data = new StringLiteral(
            '[
                {"category": "base", "price": 10.5, "priceCurrency": "EUR"},
                {"category": "base", "price": 15, "priceCurrency": "EUR"}
            ]'
        );

        $this->setExpectedException(
            \Exception::class,
            'priceInfo should not contain more than one item with the "base" category.'
        );

        $this->deserializer->deserialize($data);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_no_base_price_is_found()
    {
        $data = new StringLiteral(
            '[{"name": "Senioren", "category": "tariff", "price": 10.5, "priceCurrency": "EUR"}]'
        );

        $this->setExpectedException(
            \Exception::class,
            'priceInfo should contain one item with the "base" category.'
        );

        $this->deserializer->deserialize($data);
    }

    /**
     * @test
     */
    public function it_deserializes_valid_price_info_data()
    {
        $data = new StringLiteral(
            '[
                {"category": "base", "price": 15, "priceCurrency": "EUR"},
                {"category": "tarrif", "name": "Werkloze dodo kwekers", "price": 0, "priceCurrency": "EUR"}
            ]'
        );

        $basePrice = new BasePrice(
            new Price(1500),
            Currency::fromNative('EUR')
        );

        $tariff = new Tariff(
            new StringLiteral('Werkloze dodo kwekers'),
            new Price(0),
            Currency::fromNative('EUR')
        );

        $expectedPriceInfo = (new PriceInfo($basePrice))
            ->withExtraTariff($tariff);

        $actualPriceInfo = $this->deserializer->deserialize($data);

        $this->assertEquals($expectedPriceInfo, $actualPriceInfo);
    }
}
