<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Calendar;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\UDB3\Symfony\Deserializer\Calendar\Validators\StartDateEndDateValidator;
use CultuurNet\UDB3\Symfony\Deserializer\Calendar\Validators\TimeSpanValidator;
use CultuurNet\UDB3\Symfony\Deserializer\DataValidator\DataValidatorInterface;

class CalendarForEventDataValidator implements DataValidatorInterface
{
    /**
     * @param array $data
     * @throws DataValidationException
     */
    public function validate(array $data)
    {
        $messages = [];

        $calendarJSONParser = new CalendarJSONParser();

        // For an event the following specific rules apply:
        // - Empty data is not allowed
        // - If a start date is given then an end date is also needed
        // - If an end date is given then a start date is also needed
        // - When multiple time spans no opening hours

        if (count($data) === 0) {
            $messages['permanent'] = 'Permanent events are not supported.';
        }

        $messages = array_merge(
            $messages,
            (new StartDateEndDateValidator())->validate($data)
        );

        $messages = array_merge(
            $messages,
            (new TimeSpanValidator())->validate($data)
        );

        if (count($calendarJSONParser->getTimeSpans($data)) > 0 && count($calendarJSONParser->getOpeningHours($data)) > 0) {
            $messages['opening_hours'] = 'When opening hours are given no time spans are allowed.';
        }

        if (!empty($messages)) {
            $e = new DataValidationException();
            $e->setValidationMessages($messages);
            throw $e;
        }
    }
}
