<?php

namespace CultuurNet\UDB3\Symfony\Deserializer;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use DateTime;

class CalendarDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CalendarDeserializer
     */
    protected $deserializer;

    public function setup()
    {
        $this->deserializer = new CalendarDeserializer();
    }


    /**
     * @test
     */
    public function it_should_deserialize_a_calendar_with_a_single_date()
    {
        $majorInfoWithCalendarData = json_decode(file_get_contents(__DIR__ . '/major-info-data.json'));

        $expectedCalendar = new Calendar(
            CalendarType::SINGLE(),
            DateTime::createFromFormat(DateTime::ATOM, '2016-10-04T13:00:00Z'),
            DateTime::createFromFormat(DateTime::ATOM, '2016-10-04T23:59:59Z')
        );

        $calendar = $this->deserializer->deserialize($majorInfoWithCalendarData);

        $this->assertEquals($expectedCalendar, $calendar);
    }
}
