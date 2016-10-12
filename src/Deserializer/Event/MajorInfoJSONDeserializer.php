<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Event;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Symfony\Deserializer\CalendarDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\Location\LocationJSONDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\Theme\ThemeJSONDeserializer;
use CultuurNet\UDB3\Theme;
use CultuurNet\UDB3\Title;
use ValueObjects\String\String as StringLiteral;

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
        $this->calendarDeserializer = new CalendarDeserializer();
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

        $calendar = $this->calendarDeserializer->deserialize($data);

        $theme = null;
        if (!empty($data['theme'])) {
            $theme = $this->themeDeserializer->deserialize(
                new StringLiteral(json_encode($data['theme']))
            );
        }

        return new MajorInfo(
            new Title($data['name']['nl']),
            $type,
            $location,
            $calendar,
            $theme
        );
    }
}
