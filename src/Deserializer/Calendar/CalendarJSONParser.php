<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Calendar;

use CultuurNet\UDB3\Calendar\DayOfWeekCollection;
use CultuurNet\UDB3\Calendar\OpeningHour;
use CultuurNet\UDB3\Calendar\OpeningTime;
use CultuurNet\UDB3\Timestamp;

class CalendarJSONParser implements CalendarJSONParserInterface
{
    /**
     * @param array $data
     *
     * @return \DateTimeInterface|null
     */
    public function getStartDate($data)
    {
        if (!isset($data['startDate'])) {
            return null;
        }

        return new \DateTime($data['startDate']);
    }

    /**
     * @param array $data
     *
     * @return \DateTimeInterface|null
     */
    public function getEndDate($data)
    {
        if (!isset($data['startDate'])) {
            return null;
        }

        return new \DateTime($data['endDate']);
    }

    /**
     * @param array $data
     *
     * @return Timestamp[]
     */
    public function getTimeStamps($data)
    {
        $timestamps = [];

        if (!empty($data['timestamps'])) {
            foreach ($data['timestamps'] as $index => $timestamp) {
                $startDate = new \DateTime($timestamp['start']);
                $endDate = new \DateTime($timestamp['end']);
                $timestamps[] = new Timestamp($startDate, $endDate);
            }
            ksort($timestamps);
        }

        return $timestamps;
    }

    /**
     * @param array $data
     *
     * @return OpeningHour[]
     */
    public function getOpeningHours($data)
    {
        $openingHours = [];

        if (!empty($data['openingHours'])) {
            foreach ($data['openingHours'] as $openingHour) {
                $daysOfWeek = DayOfWeekCollection::deserialize($openingHour['dayOfWeek']);

                $openingHours[] = new OpeningHour(
                    OpeningTime::fromNativeString($openingHour['opens']),
                    OpeningTime::fromNativeString($openingHour['closes']),
                    $daysOfWeek
                );
            }
        }

        return $openingHours;
    }
}
