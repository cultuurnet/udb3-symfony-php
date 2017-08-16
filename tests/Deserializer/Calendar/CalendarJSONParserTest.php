<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Calendar;

use CultuurNet\UDB3\Calendar\DayOfWeek;
use CultuurNet\UDB3\Calendar\DayOfWeekCollection;
use CultuurNet\UDB3\Calendar\OpeningHour;
use CultuurNet\UDB3\Calendar\OpeningTime;
use CultuurNet\UDB3\Timestamp;
use ValueObjects\DateTime\Hour;
use ValueObjects\DateTime\Minute;

class CalendarJSONParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $updateCalendarAsArray;

    /**
     * @var CalendarJSONParser
     */
    private $calendarJSONParser;

    protected function setUp()
    {
        $updateCalendar = file_get_contents(__DIR__ . '/updated_calendar.json');
        $this->updateCalendarAsArray = json_decode($updateCalendar, true);

        $this->calendarJSONParser = new CalendarJSONParser();
    }

    /**
     * @test
     */
    public function it_can_get_the_start_date()
    {
        $startDate = \DateTime::createFromFormat(\DateTime::ATOM, '2020-01-26T09:00:00+01:00');

        $this->assertEquals(
            $startDate,
            $this->calendarJSONParser->getStartDate(
                $this->updateCalendarAsArray
            )
        );
    }

    /**
     * @test
     */
    public function it_can_get_the_end_date()
    {
        $endDate = \DateTime::createFromFormat(\DateTime::ATOM, '2020-02-10T16:00:00+01:00');

        $this->assertEquals(
            $endDate,
            $this->calendarJSONParser->getEndDate(
                $this->updateCalendarAsArray
            )
        );
    }

    /**
     * @test
     */
    public function it_can_get_the_timestamps()
    {
        $startDatePeriod1 = \DateTime::createFromFormat(\DateTime::ATOM, '2020-01-26T09:00:00+01:00');
        $endDatePeriod1 = \DateTime::createFromFormat(\DateTime::ATOM, '2020-02-01T16:00:00+01:00');

        $startDatePeriod2 = \DateTime::createFromFormat(\DateTime::ATOM, '2020-02-03T09:00:00+01:00');
        $endDatePeriod2 = \DateTime::createFromFormat(\DateTime::ATOM, '2020-02-10T16:00:00+01:00');

        $timeStamps = [
            new Timestamp(
                $startDatePeriod1,
                $endDatePeriod1
            ),
            new Timestamp(
                $startDatePeriod2,
                $endDatePeriod2
            ),
        ];

        $this->assertEquals(
            $timeStamps,
            $this->calendarJSONParser->getTimeStamps(
                $this->updateCalendarAsArray
            )
        );
    }

    /**
     * @test
     */
    public function it_can_get_the_opening_hours()
    {
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

        $this->assertEquals(
            $openingHours,
            $this->calendarJSONParser->getOpeningHours(
                $this->updateCalendarAsArray
            )
        );
    }
}
