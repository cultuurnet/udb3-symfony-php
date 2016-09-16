<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Event;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Symfony\Deserializer\CalendarDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\Location\LocationJSONDeserializer;
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
     * @var CalendarDeserializer
     */
    private $calendarDeserializer;

    public function __construct()
    {
        $assoc = true;
        parent::__construct($assoc);

        $this->validator = new MajorInfoDataValidator();
        $this->locationDeserializer = new LocationJSONDeserializer();
        $this->calendarDeserializer = new CalendarDeserializer();
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

        $location = $this->locationDeserializer->deserialize(
            new StringLiteral(json_encode($data['location']))
        );

        $calendar = $this->calendarDeserializer->deserialize((object) $data);

        $theme = null;
        if (!empty($data['theme'])) {
            $theme = new Theme($data['theme']['id'], $data['theme']['label']);
        }

        return new MajorInfo(
            new Title($data['name']['nl']),
            new EventType($data['type']['id'], $data['type']['label']),
            $location,
            $calendar,
            $theme
        );
    }
}
