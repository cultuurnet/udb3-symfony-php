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
            DateTime::createFromFormat(DateTime::ATOM, '2016-10-04T23:59:00+02:00')
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
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-13T23:59:00+02:00')
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
            DateTime::createFromFormat(DateTime::ATOM, '2016-10-15T23:59:00+0200'),
            [
                '1476309600' => new Timestamp(
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-13T00:00:00+0200'),
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-13T23:59:00+0200')
                ),
                '1476396000' => new Timestamp(
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-14T00:00:00+0200'),
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-14T23:59:00+0200')
                ),
                '1476482400' => new Timestamp(
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-15T00:00:00+0200'),
                    DateTime::createFromFormat(DateTime::ATOM, '2016-10-15T23:59:00+0200')
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

        $expectedCalendar = new Calendar(
            CalendarType::PERMANENT(),
            null,
            null,
            [],
            [
                [
                    'dayOfWeek' => [
                        'monday',
                        'tuesday',
                        'wednesday',
                        'thursday',
                        'friday',
                    ],
                    'opens' => '10:00',
                    'closes' => '19:00'
                ],
                [
                    'dayOfWeek' => [
                        'saturday',
                        'sunday'
                    ],
                    'opens' => '12:00',
                    'closes' => '19:00'
                ]
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
}
