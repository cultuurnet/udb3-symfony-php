<?php

namespace CultuurNet\UDB3\Symfony\Deserializer;

use CultuurNet\UDB3\Calendar;
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
                '1476396000' => new Timestamp(
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
}
