<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\BookingInfo;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\UDB3\Symfony\Deserializer\DataValidator\DataValidatorInterface;

class BookingInfoDataValidator implements DataValidatorInterface
{
    /**
     * @param array $data
     * @return array
     * @throws DataValidationException
     */
    public function validate(array $data)
    {
        if (!isset($data['bookingInfo'])) {
            $e = new DataValidationException();
            $e->setValidationMessages(['bookingInfo' => 'Required but could not be found.']);
            throw $e;
        }

        $bookingInfo = $data['bookingInfo'];

        $messages = [];
        $availabilityFormatError = 'Invalid format. Expected ISO-8601 (eg. 2018-01-01T00:00:00+01:00).';

        if (isset($bookingInfo['availabilityStarts'])) {
            $dateTime = \DateTimeImmutable::createFromFormat(\DATE_ATOM, $bookingInfo['availabilityStarts']);

            if (!$dateTime) {
                $messages['bookingInfo.availabilityStarts'] = $availabilityFormatError;
            }
        }

        if (isset($bookingInfo['availabilityEnds'])) {
            $dateTime = \DateTimeImmutable::createFromFormat(\DATE_ATOM, $bookingInfo['availabilityEnds']);

            if (!$dateTime) {
                $messages['bookingInfo.availabilityEnds'] = $availabilityFormatError;
            }
        }

        if (!empty($messages)) {
            $e = new DataValidationException();
            $e->setValidationMessages($messages);
            throw $e;
        }
    }
}
