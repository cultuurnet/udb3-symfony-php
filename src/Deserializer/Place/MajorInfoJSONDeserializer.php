<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Place;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\UDB3\Event\EventType;
use CultuurNet\UDB3\Symfony\Deserializer\Address\AddressJSONDeserializer;
use CultuurNet\UDB3\Symfony\Deserializer\CalendarDeserializer;
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
     * @var AddressJSONDeserializer
     */
    private $addressDeserializer;

    /**
     * @var CalendarDeserializer
     */
    private $calendarDeserializer;

    public function __construct()
    {
        $assoc = true;
        parent::__construct($assoc);

        $this->validator = new MajorInfoDataValidator();
        $this->addressDeserializer = new AddressJSONDeserializer();
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

        $address = $this->addressDeserializer->deserialize(
            new StringLiteral(json_encode($data['address']))
        );

        $calendar = $this->calendarDeserializer->deserialize((object) $data);

        $theme = null;
        if (!empty($data['theme'])) {
            $theme = new Theme($data['theme']['id'], $data['theme']['label']);
        }

        return new MajorInfo(
            new Title($data['name']['nl']),
            new EventType($data['type']['id'], $data['type']['label']),
            $address,
            $calendar,
            $theme
        );
    }
}
