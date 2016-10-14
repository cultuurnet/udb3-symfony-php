<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Location;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Location\Location;
use ValueObjects\Geography\Country;
use ValueObjects\String\String as StringLiteral;

class LocationJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocationJSONDeserializer
     */
    private $deserializer;

    public function setUp()
    {
        $this->deserializer = new LocationJSONDeserializer();
    }

    /**
     * @test
     */
    public function it_checks_all_required_fields_are_present()
    {
        $data = new StringLiteral('{}');

        $expectedException = new DataValidationException();
        $expectedException->setValidationMessages(
            [
                'id' => 'Required but could not be found.',
                'name' => 'Required but could not be found.',
                'address' => 'Required but could not be found.',
            ]
        );

        $this->deserializeAndExpectException($data, $expectedException);
    }

    /**
     * @test
     */
    public function it_validates_address_properties()
    {
        $json = '{"id": "7e81e097-ce33-4e96-8a8a-abfd87b82894", "name": "foo", "address": {"postalCode":1000}}';
        $data = new StringLiteral($json);

        $expectedException = new DataValidationException();
        $expectedException->setValidationMessages(
            [
                'address.streetAddress' => 'Required but could not be found.',
                'address.addressLocality' => 'Required but could not be found.',
                'address.addressCountry' => 'Required but could not be found.',
            ]
        );

        $this->deserializeAndExpectException($data, $expectedException);
    }

    /**
     * @test
     */
    public function it_returns_a_location_object()
    {
        $data = new StringLiteral(
            json_encode(
                [
                    'id' => '3941e3b6-3044-4b6c-a1af-f3a97e8af92d',
                    'name' => 'Praatcafé de Sjoemelaar',
                    'address' => [
                        'streetAddress' => 'Wetstraat 1',
                        'postalCode' => '1000',
                        'addressLocality' => 'Brussel',
                        'addressCountry' => 'BE',
                    ],
                ]
            )
        );

        $expectedLocation = new Location(
            '3941e3b6-3044-4b6c-a1af-f3a97e8af92d',
            new StringLiteral('Praatcafé de Sjoemelaar'),
            new Address(
                Street::fromNative('Wetstraat 1'),
                PostalCode::fromNative('1000'),
                Locality::fromNative('Brussel'),
                Country::fromNative('BE')
            )
        );

        $actualLocation = $this->deserializer->deserialize($data);

        $this->assertEquals($expectedLocation, $actualLocation);
    }

    /**
     * @param StringLiteral $data
     * @param DataValidationException $expectedException
     */
    private function deserializeAndExpectException(StringLiteral $data, DataValidationException $expectedException)
    {
        try {
            $this->deserializer->deserialize($data);
            $this->fail("No DataValidationException was thrown.");
        } catch (\Exception $e) {
            /* @var DataValidationException $e */
            $this->assertInstanceOf(DataValidationException::class, $e);
            $this->assertEquals($expectedException->getValidationMessages(), $e->getValidationMessages());
        }
    }
}
