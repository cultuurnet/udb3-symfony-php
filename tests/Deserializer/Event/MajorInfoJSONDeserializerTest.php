<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Event;

use CultuurNet\UDB3\Address\Address;
use CultuurNet\UDB3\Address\Locality;
use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Address\Street;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Location\Location;
use ValueObjects\Geography\Country;
use ValueObjects\StringLiteral\StringLiteral;

class MajorInfoJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_serialize_major_info()
    {
        $majorInfoAsJson = file_get_contents(__DIR__ . '/../samples/event-major-info.json');

        $majorInfoJSONDeserializer = new MajorInfoJSONDeserializer();

        $majorInfo = $majorInfoJSONDeserializer->deserialize(new StringLiteral($majorInfoAsJson));

        $expectedLocation = new Location(
            '28cf728d-441b-4912-b3b0-f03df0d22491',
            new StringLiteral('Hier'),
            new Address(
                new Street('Daarstraat 1'),
                new PostalCode('3000'),
                new Locality('Leuven'),
                Country::fromNative('BE')
            )
        );

        $this->assertEquals('talking title', $majorInfo->getTitle());
        $this->assertEquals(new EventType('0.17.0.0.0', 'Route'), $majorInfo->getType());
        $this->assertEquals($expectedLocation, $majorInfo->getLocation());
        $this->assertEquals(new Calendar(CalendarType::PERMANENT()), $majorInfo->getCalendar());
    }
}
