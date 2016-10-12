<?php

namespace CultuurNet\UDB3\Symfony\Deserializer;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Timestamp;
use DateTime;

/**
 * @todo Extend JSONDeserializer, and clean up.
 */
class CalendarDeserializer
{
    public function deserialize($eventData)
    {
        // Cleanup empty timestamps.
        $timestamps = array();
        if (!empty($eventData->timestamps)) {
            foreach ($eventData->timestamps as $timestamp) {
                if (!empty($timestamp->date)) {
                    $date = date('Y-m-d', strtotime($timestamp->date));

                    // Check if a correct starthour is given.
                    if (!empty($timestamp->showStartHour) && !empty($timestamp->startHour)) {
                        list($hour, $minute) = explode(':', $timestamp->startHour);
                        if (strlen($hour) == 2 && strlen($minute) == 2) {
                            $startDate = $date . 'T' . $timestamp->startHour . ':00Z';
                        } else {
                            $startDate = $date . 'T00:00:00Z';
                        }
                    } else {
                        $startDate = $date . 'T00:00:00Z';
                    }

                    // Check if a correct endhour is given.
                    if (!empty($timestamp->showEndHour) && !empty($timestamp->endHour)) {
                        list($hour, $minute) = explode(':', $timestamp->endHour);
                        if (strlen($hour) == 2 && strlen($minute) == 2) {
                            $endDate = $date . 'T' . $timestamp->endHour . ':00Z';
                        } else {
                            $endDate = $date . 'T23:59:59Z';
                        }
                    } else {
                        $endDate = $date . 'T23:59:59Z';
                    }

                    $timestamps[strtotime($startDate)] = new Timestamp(
                        DateTime::createFromFormat(DateTime::ATOM, $startDate),
                        DateTime::createFromFormat(DateTime::ATOM, $endDate)
                    );
                }
            }
            ksort($timestamps);
        }

        $startDate = !empty($eventData->startDate)
            ? DateTime::createFromFormat(DateTime::ATOM, $eventData->startDate)
            : null;
        $endDate = !empty($eventData->endDate)
            ? DateTime::createFromFormat(DateTime::ATOM, $eventData->endDate)
            : null;

        // For single calendar type, check if it should be multiple
        // Also calculate the correct startDate and endDate for the calendar object.
        $calendarType = !empty($eventData->calendarType)
            ? CalendarType::fromNative($eventData->calendarType)
            : CalendarType::PERMANENT();

        if ($calendarType->is(CalendarType::SINGLE()) && count($timestamps) == 1) {
            // 1 timestamp = no timestamps needed. Copy start and enddate.
            $firstTimestamp = current($timestamps);
            $startDate = $firstTimestamp->getStartDate();
            $endDate = $firstTimestamp->getEndDate();
            $timestamps = array();
        } elseif ($calendarType->is(CalendarType::SINGLE()) && count($timestamps) > 1) {
            // Multiple timestamps, startDate = first date, endDate = last date.
            $calendarType = CalendarType::MULTIPLE();
            $firstTimestamp = current($timestamps);
            $lastTimestamp = end($timestamps);
            $startDate = $firstTimestamp->getStartDate();
            $endDate = $lastTimestamp->getEndDate();
        }

        // Remove empty opening hours.
        $openingHours = array();
        if (!empty($eventData->openingHours)) {
            $openingHours = $eventData->openingHours;
            foreach ($openingHours as $key => $openingHour) {
                if (empty($openingHour->dayOfWeek) || empty($openingHour->opens) || empty($openingHour->closes)) {
                    unset($openingHours[$key]);
                }
            }
        }

        return new Calendar($calendarType, $startDate, $endDate, $timestamps, $openingHours);
    }
}
