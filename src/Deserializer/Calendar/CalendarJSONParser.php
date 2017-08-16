<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Calendar;

use CultuurNet\UDB3\Calendar\DayOfWeekCollection;
use CultuurNet\UDB3\Calendar\OpeningHour;
use CultuurNet\UDB3\Calendar\OpeningTime;
use CultuurNet\UDB3\Timestamp;

class CalendarJSONParser
{
    /**
     * @var array
     */
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getStartDate()
    {
        return new \DateTime($this->data['startDate']);
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEndDate()
    {
        return new \DateTime($this->data['endDate']);
    }

    /**
     * @return TimeStamp[]
     */
    public function getTimeStamps()
    {
        $timestamps = [];

        if (!empty($this->data['timestamps'])) {
            foreach ($this->data['timestamps'] as $index => $timestamp) {
                $startDate = new \DateTime($timestamp['start']);
                $endDate = new \DateTime($timestamp['end']);
                $timestamps[] = new Timestamp($startDate, $endDate);
            }
            ksort($timestamps);
        }

        return $timestamps;
    }

    /**
     * @return OpeningHour[]
     */
    public function getOpeningHours()
    {
        $openingHours = [];

        if (!empty($this->data['openingHours'])) {
            foreach ($this->data['openingHours'] as $openingHour) {
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
