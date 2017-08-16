<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Calendar;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Calendar\DayOfWeek;
use CultuurNet\UDB3\Calendar\DayOfWeekCollection;
use CultuurNet\UDB3\Calendar\OpeningHour;
use CultuurNet\UDB3\Calendar\OpeningTime;
use CultuurNet\UDB3\CalendarType;
use ValueObjects\DateTime\Hour;
use ValueObjects\DateTime\Minute;
use ValueObjects\StringLiteral\StringLiteral;

class CalendarJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_deserialize_json_to_calendar()
    {
        $calendarAsJsonString = new StringLiteral(
            file_get_contents(__DIR__ . '/calendar.json')
        );

        $calendarJSONDeserializer = new CalendarJSONDeserializer(
            new CalendarJSONParser()
        );

        $openingHours = [
            new OpeningHour(
                new OpeningTime(
                    new Hour(9),
                    new Minute(0)
                ),
                new OpeningTime(
                    new Hour(17),
                    new Minute(0)
                ),
                new DayOfWeekCollection(
                    DayOfWeek::TUESDAY(),
                    DayOfWeek::WEDNESDAY(),
                    DayOfWeek::THURSDAY(),
                    DayOfWeek::FRIDAY()
                )
            ),
            new OpeningHour(
                new OpeningTime(
                    new Hour(9),
                    new Minute(0)
                ),
                new OpeningTime(
                    new Hour(12),
                    new Minute(0)
                ),
                new DayOfWeekCollection(
                    DayOfWeek::SATURDAY()
                )
            ),
        ];

        $expectedCalendar = new Calendar(
            CalendarType::PERIODIC(),
            \DateTime::createFromFormat(\DateTime::ATOM, '2020-01-26T09:00:00+01:00'),
            \DateTime::createFromFormat(\DateTime::ATOM, '2020-02-10T16:00:00+01:00'),
            [],
            $openingHours
        );

        $this->assertEquals(
            $expectedCalendar,
            $calendarJSONDeserializer->deserialize($calendarAsJsonString)
        );
    }
}
