<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Calendar;

use CultuurNet\Deserializer\DataValidationException;

class CalendarForEventDataValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CalendarForEventDataValidator
     */
    private $calendarForEventDataValidator;

    protected function setUp()
    {
        $this->calendarForEventDataValidator = new CalendarForEventDataValidator();
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param array $data
     * @param array $messages
     */
    public function it_throws_when_time_spans_are_present(
        array $data,
        array $messages
    ) {
        $expectedException = new DataValidationException();
        $expectedException->setValidationMessages($messages);

        try {
            $this->calendarForEventDataValidator->validate($data);
            $this->fail("No DataValidationException was thrown.");
        } catch (\Exception $exception) {
            /* @var DataValidationException $exception */
            $this->assertInstanceOf(DataValidationException::class, $exception);
            $this->assertEquals(
                $expectedException->getValidationMessages(),
                $exception->getValidationMessages()
            );
        }
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'it_throws_when_permanent' => [
                'data' => [
                ],
                'messages' => [
                    'permanent' => 'Permanent events are not supported.',
                ],
            ],
            'it_throws_when_end_date_is_missing' => [
                'data' => [
                    'startDate' => '2020-01-26T09:00:00+01:00',
                ],
                'messages' => [
                    'end_date' => 'When a start date is given then an end date is also required.',
                ],
            ],
            'it_throws_when_start_date_is_missing' => [
                'data' => [
                    'endDate' => '2020-02-10T16:00:00+01:00',
                ],
                'messages' => [
                    'start_date' => 'When an end date is given then a start date is also required.',
                ],
            ],
            'it_throws_time_spans_and_opening_hours' => [
                'data' => [
                    'timeSpans' => [
                        [
                            'start' => '2020-01-26T09:00:00+01:00',
                            'end' => '2020-02-01T16:00:00+01:00',
                        ],
                        [
                            'start' => '2020-02-03T09:00:00+01:00',
                            'end' => '2020-02-10T16:00:00+01:00',
                        ],
                    ],
                    'openingHours' => [
                        [
                            'opens' => '09:00',
                            'closes' => '17:00',
                            'dayOfWeek' => [
                                'tuesday',
                                'wednesday',
                            ]
                        ],
                    ]
                ],
                'messages' => [
                    'opening_hours' => 'When opening hours are given no time spans are allowed.',
                ],
            ],
        ];
    }
}
