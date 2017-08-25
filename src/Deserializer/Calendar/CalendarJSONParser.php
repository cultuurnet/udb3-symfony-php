<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Calendar;

use CultuurNet\UDB3\Calendar\DayOfWeekCollection;
use CultuurNet\UDB3\Calendar\OpeningHour;
use CultuurNet\UDB3\Calendar\OpeningTime;

class CalendarJSONParser implements CalendarJSONParserInterface
{
    /**
     * @param array $data
     *
     * @return \DateTimeInterface|null
     */
    public function getStartDate($data)
    {
        if (isset($data['startDate'])) {
            return new \DateTime($data['startDate']);
        }

        $timeSpans = $this->getTimeSpans($data);
        if (count($timeSpans) > 0) {
            return $timeSpans[0]->getStart();
        }

        return null;
    }

    /**
     * @param array $data
     *
     * @return \DateTimeInterface|null
     */
    public function getEndDate($data)
    {
        if (isset($data['startDate'])) {
            return new \DateTime($data['endDate']);
        }

        $timeSpans = $this->getTimeSpans($data);
        if (count($timeSpans) > 0) {
            return $timeSpans[count($timeSpans) - 1]->getEnd();
        }

        return null;
    }

    /**
     * @param array $data
     *
     * @return TimeSpan[]
     */
    public function getTimeSpans($data)
    {
        $timestamps = [];

        if (!empty($data['timeSpans'])) {
            foreach ($data['timeSpans'] as $index => $timestamp) {
                $startDate = new \DateTime($timestamp['start']);
                $endDate = new \DateTime($timestamp['end']);
                $timestamps[] = new TimeSpan($startDate, $endDate);
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
