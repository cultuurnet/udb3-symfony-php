<?php

namespace CultuurNet\UDB3\Symfony\Deserializer;

use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Timestamp;
use DateTime;
use InvalidArgumentException;
use ValueObjects\DateTime\Date;

/**
 * @todo Extend JSONDeserializer, and clean up.
 */
class CalendarDeserializer
{
    public function deserialize($eventData)
    {
        // Cleanup empty timestamps.
        $timestamps = array();
        if (!empty($eventData['timestamps'])) {
            foreach ($eventData['timestamps'] as $timestamp) {
                if (!empty($timestamp['date'])) {
                    $time = strtotime($timestamp['date']);

                    if (!$time) {
                        throw new InvalidArgumentException('Invalid date string provided for timestamp, ISO8601 expected!');
                    }

                    $dayTime = (new DateTime())->setTimestamp($time);

                    // Check if a correct starthour is given.
                    if (!empty($timestamp['showStartHour']) && !empty($timestamp['startHour'])) {
                        list($hour, $minute) = explode(':', $timestamp['startHour']);
                        if (strlen($hour) == 2 && strlen($minute) == 2) {
                            $startTime = clone $dayTime;
                            $startTime->setTime(intval($hour), intval($minute));
                        }
                    }
                    $startTime = isset($startTime) ? $startTime : $dayTime;


                    // Check if a correct endhour is given.
                    if (!empty($timestamp['showEndHour']) && !empty($timestamp['endHour'])) {
                        list($hour, $minute) = explode(':', $timestamp['endHour']);
                        if (strlen($hour) == 2 && strlen($minute) == 2) {
                            $endTime = clone $dayTime;
                            $endTime->setTime(intval($hour), intval($minute));
                        }
                    }
                    $endTime = isset($endTime) ? $endTime : $dayTime;


                    // Make sure the the timestamp does not end in the past
                    // If it does, push the end forward to when the timestamp starts
                    $endTime = ($endTime > $startTime) ? $endTime : $startTime;

                    $timestamps[$time] = new Timestamp($startTime, $endTime);
                }
            }
            ksort($timestamps);
        }

        $startDate = !empty($eventData['startDate'])
            ? (new DateTime())->setTimestamp(strtotime($eventData['startDate']))
            : null;

        $endDate = !empty($eventData['endDate'])
            ? (new DateTime())->setTimestamp(strtotime($eventData['endDate']))
            : null;

        // For single calendar type, check if it should be multiple
        // Also calculate the correct startDate and endDate for the calendar object.
        $calendarType = !empty($eventData['calendarType'])
            ? CalendarType::fromNative($eventData['calendarType'])
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
        if (!empty($eventData['endHour'])) {
            $openingHours = $eventData['endHour'];
            foreach ($openingHours as $key => $openingHour) {
                if (empty($openingHour['dayOfWeek']) || empty($openingHour['opens']) || empty($openingHour['closes'])) {
                    unset($openingHours[$key]);
                }
            }
        }

        return new Calendar($calendarType, $startDate, $endDate, $timestamps, $openingHours);
    }
}
