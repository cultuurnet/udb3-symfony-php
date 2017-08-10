<?php

namespace CultuurNet\UDB3\Symfony\Deserializer;

use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use ValueObjects\StringLiteral\StringLiteral;

class CalendarJSONDeserializer extends JSONDeserializer
{
    /**
     * @param StringLiteral $data
     * @return Calendar
     */
    public function deserialize(StringLiteral $data)
    {
        return new Calendar(CalendarType::PERMANENT());
    }
}
