<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Address;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use ValueObjects\Geography\Country;
use ValueObjects\String\String as StringLiteral;

class AddressJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressJSONDeserializer
     */
    private $deserializer;

    public function setUp()
    {
        $this->deserializer = new AddressJSONDeserializer();
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
                'streetAddress' => 'Required but could not be found.',
                'postalCode' => 'Required but could not be found.',
                'addressLocality' => 'Required but could not be found.',
                'addressCountry' => 'Required but could not be found.',
            ]
        );

        try {
            $this->deserializer->deserialize($data);
            $this->fail("No DataValidationException was thrown.");
        } catch (\Exception $e) {
            /* @var DataValidationException $e */
            $this->assertInstanceOf(DataValidationException::class, $e);
            $this->assertEquals($expectedException->getValidationMessages(), $e->getValidationMessages());
        }
    }

    /**
     * @test
     */
    public function it_returns_an_address_object()
    {
        $data = new StringLiteral(
            json_encode(
                [
                    'streetAddress' => 'Wetstraat 1',
                    'postalCode' => '1000',
                    'addressLocality' => 'Brussel',
                    'addressCountry' => 'BE',
                ]
            )
        );

        $expectedAddress = new Address(
            Street::fromNative('Wetstraat 1'),
            PostalCode::fromNative('1000'),
            Locality::fromNative('Brussel'),
            Country::fromNative('BE')
        );

        $actualAddress = $this->deserializer->deserialize($data);

        $this->assertEquals($expectedAddress, $actualAddress);
    }
}
