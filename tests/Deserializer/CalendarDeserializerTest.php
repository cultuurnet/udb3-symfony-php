<?php

namespace CultuurNet\UDB3\Symfony\Deserializer;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\Calendar\DayOfWeek;
use CultuurNet\UDB3\Calendar\DayOfWeekCollection;
use CultuurNet\UDB3\Calendar\OpeningHour;
use CultuurNet\UDB3\Calendar\OpeningTime;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Timestamp;
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
        date_default_timezone_set('Europe/Brussels');
    }

    /**
     * @test
     */
    public function it_should_deserialize_a_calendar_with_a_single_date()
    {
        $majorInfoWithCalendarData = json_decode(
            file_get_contents(__DIR__ . '/samples/major-info-data-with-single-day.json'),
            true
        );

        $expectedCalendar = new Calendar(
            CalendarType::SINGLE(),
            DateTime::createFromFormat(DateTime::ATOM, '2016-10-04T13:00:00+02:00'),
            DateTime::createFromFormat(DateTime::ATOM, '2016-10-04T13:00:00+02:00')
        );

        $calendar = $this->deserializer->deserialize($majorInfoWithCalendarData);

        $this->assertEquals($expectedCalendar, $calendar);
    }

    /**
     * @test
     */
    public function it_should_deserialize_a_calendar_with_a_multiple_dates()
    {
        $majorInfoWithCalendarData = json_decode(
            file_get_contents(__DIR__ . '/samples/major-info-data-with-multiple-days.json'),
            true
        );

        $expectedCalendar = new Calendar(
            CalendarType::MULTIPLE(),
            DateTime::createFromFormat(DateTime::ATOM, '2016-10-13T14:00:00+02:00'),
            DateTime::createFromFormat(DateTime::ATOM, '2016-10-14T13:00:00+02:00'),
            [
                '1476309600' => new Timestamp(
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-13T14:00:00+02:00'),
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-13T14:00:00+02:00')
                ),
                '1476396001' => new Timestamp(
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-14T09:00:00+02:00'),
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-14T13:00:00+02:00')
                )
            ]
        );

        $calendar = $this->deserializer->deserialize($majorInfoWithCalendarData);

        $this->assertEquals($expectedCalendar, $calendar);
    }

    /**
     * @test
     */
    public function it_should_deserialize_a_periodic_calendar()
    {
        $majorInfoWithCalendarData = json_decode(
            file_get_contents(__DIR__ . '/samples/major-info-data-with-periodic-calendar.json'),
            true
        );

        $expectedCalendar = new Calendar(
            CalendarType::PERIODIC(),
            DateTime::createFromFormat(DateTime::ATOM, '2016-10-06T00:00:00+02:00'),
            DateTime::createFromFormat(DateTime::ATOM, '2016-10-07T00:00:00+02:00')
        );

        $calendar = $this->deserializer->deserialize($majorInfoWithCalendarData);

        $this->assertEquals($expectedCalendar, $calendar);
    }

    /**
     * @test
     */
    public function it_should_deserialize_calendar_info_with_multiple_days_and_no_hours()
    {
        $majorInfoWithCalendarData = json_decode(
            file_get_contents(__DIR__ . '/samples/major-info-data-with-multiple-days-without-hours.json'),
            true
        );

        $expectedCalendar = new Calendar(
            CalendarType::MULTIPLE(),
            DateTime::createFromFormat(DateTime::ATOM, '2016-10-13T00:00:00+0200'),
            DateTime::createFromFormat(DateTime::ATOM, '2016-10-15T00:00:00+0200'),
            [
                '1476309600' => new Timestamp(
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-13T00:00:00+0200'),
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-13T00:00:00+0200')
                ),
                '1476396001' => new Timestamp(
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-14T00:00:00+0200'),
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-14T00:00:00+0200')
                ),
                '1476482402' => new Timestamp(
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-15T00:00:00+0200'),
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-15T00:00:00+0200')
                )
            ]
        );

        $calendar = $this->deserializer->deserialize($majorInfoWithCalendarData);

        $this->assertEquals($expectedCalendar, $calendar);
    }

    /**
     * @test
     */
    public function it_should_deserialize_opening_hours()
    {
        $majorInfoWithOpeningHoursData = json_decode(
            file_get_contents(__DIR__ . '/samples/major-info-data-with-opening-hours.json'),
            true
        );

        $weekDays = new DayOfWeekCollection(
            DayOfWeek::MONDAY(),
            DayOfWeek::TUESDAY(),
            DayOfWeek::WEDNESDAY(),
            DayOfWeek::THURSDAY(),
            DayOfWeek::FRIDAY()
        );

        $weekendDays = new DayOfWeekCollection(
            DayOfWeek::SATURDAY(),
            DayOfWeek::SUNDAY()
        );

        $expectedCalendar = new Calendar(
            CalendarType::PERMANENT(),
            null,
            null,
            [],
            [
                new OpeningHour(
                    OpeningTime::fromNativeString('10:00'),
                    OpeningTime::fromNativeString('19:00'),
                    $weekDays
                ),
                new OpeningHour(
                    OpeningTime::fromNativeString('12:00'),
                    OpeningTime::fromNativeString('19:00'),
                    $weekendDays
                ),
            ]
        );

        $calendar = $this->deserializer->deserialize($majorInfoWithOpeningHoursData);

        $this->assertEquals($expectedCalendar, $calendar);
    }

    /**
     * @test
     */
    public function it_ignores_empty_opening_hours()
    {
        $majorInfoWithEmptyOpeningHoursData = json_decode(
            file_get_contents(__DIR__ . '/samples/major-info-data-with-empty-opening-hours.json'),
            true
        );

        $expectedCalendar = new Calendar(
            CalendarType::PERMANENT(),
            null,
            null,
            [],
            []
        );

        $calendar = $this->deserializer->deserialize($majorInfoWithEmptyOpeningHoursData);

        $this->assertEquals($expectedCalendar, $calendar);
    }

    /**
     * @test
     */
    public function it_should_deserialize_multiple_timestamps_per_day()
    {
        $majorInfoData = json_decode(
            file_get_contents(__DIR__ . '/samples/major-info-data-with-multiple-timestamps-per-day.json'),
            true
        );

        $expectedCalendar = new Calendar(
            CalendarType::MULTIPLE(),
            DateTime::createFromFormat(DateTime::ATOM, '2017-02-28T09:00:00+0100'),
            DateTime::createFromFormat(DateTime::ATOM, '2017-03-01T14:00:00+0100'),
            [
                '1488236400' => new Timestamp(
                    DateTime::createFromFormat(DateTime::ATOM, '2017-02-28T09:00:00+0100'),
                    DateTime::createFromFormat(DateTime::ATOM, '2017-02-28T11:00:00+0100')
                ),
                '1488236402' => new Timestamp(
                    DateTime::createFromFormat(DateTime::ATOM, '2017-02-28T12:00:00+0100'),
                    DateTime::createFromFormat(DateTime::ATOM, '2017-02-28T14:00:00+0100')
                ),
                '1488236403' => new Timestamp(
                    DateTime::createFromFormat(DateTime::ATOM, '2017-02-28T16:00:00+0100'),
                    DateTime::createFromFormat(DateTime::ATOM, '2017-02-28T18:00:00+0100')
                ),
                '1488322801' => new Timestamp(
                    DateTime::createFromFormat(DateTime::ATOM, '2017-02-29T12:00:00+0100'),
                    DateTime::createFromFormat(DateTime::ATOM, '2017-02-29T14:00:00+0100')
                ),
            ]
        );

        $calendar = $this->deserializer->deserialize($majorInfoData);

        $this->assertEquals($expectedCalendar, $calendar);
    }
}
