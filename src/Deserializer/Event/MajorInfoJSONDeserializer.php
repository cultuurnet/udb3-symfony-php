<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Event;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\Calendar\CalendarForEventDataValidator;
use CultuurNet\UDB3\Symfony\Deserializer\Calendar\CalendarJSONDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\Calendar\CalendarJSONParser;
use CultuurNet\UDB3\Symfony\Deserializer\CalendarDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\Location\LocationJSONDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\Theme\ThemeJSONDeserializer;
use CultuurNet\UDB3\Title;
use ValueObjects\StringLiteral\StringLiteral;

class MajorInfoJSONDeserializer extends JSONDeserializer
{
    /**
     * @var MajorInfoDataValidator
     */
    private $validator;

    /**
     * @var LocationJSONDeserializer
     */
    private $locationDeserializer;

    /**
     * @var EventTypeJSONDeserializer
     */
    private $typeDeserializer;

    /**
     * @var CalendarDeserializer
     */
    private $calendarDeserializer;

    /**
     * @var ThemeJSONDeserializer
     */
    private $themeDeserializer;

    public function __construct()
    {
        $assoc = true;
        parent::__construct($assoc);

        $this->validator = new MajorInfoDataValidator();

        $this->typeDeserializer = new EventTypeJSONDeserializer();
        $this->locationDeserializer = new LocationJSONDeserializer();
        $this->calendarDeserializer = new CalendarJSONDeserializer(
            new CalendarJSONParser(),
            new CalendarForEventDataValidator()
        );
        $this->themeDeserializer = new ThemeJSONDeserializer();
    }

    /**
     * @param StringLiteral $data
     * @return MajorInfo
     * @throws DataValidationException
     */
    public function deserialize(StringLiteral $data)
    {
        $data = parent::deserialize($data);
        $this->validator->validate($data);

        $type = $this->typeDeserializer->deserialize(
            new StringLiteral(json_encode($data['type']))
        );

        $location = $this->locationDeserializer->deserialize(
            new StringLiteral(json_encode($data['location']))
        );

        $calendar = $this->calendarDeserializer->deserialize(
            new StringLiteral(json_encode($data['calendar']))
        );

        $theme = null;
        if (!empty($data['theme'])) {
            $theme = $this->themeDeserializer->deserialize(
                new StringLiteral(json_encode($data['theme']))
            );
        }

        return new MajorInfo(
            new Title($data['name']),
            $type,
            $location,
            $calendar,
            $theme
        );
    }
}
