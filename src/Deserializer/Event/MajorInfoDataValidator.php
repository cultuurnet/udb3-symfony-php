<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Event;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\UDB3\Symfony\Deserializer\DataValidator\CompositeDataValidator;
use CultuurNet\UDB3\Symfony\Deserializer\DataValidator\DataValidatorInterface;
use CultuurNet\UDB3\Symfony\Deserializer\DataValidator\RequiredPropertiesDataValidator;
use CultuurNet\UDB3\Symfony\Deserializer\Location\LocationDataValidator;
use CultuurNet\UDB3\Symfony\Deserializer\Theme\ThemeDataValidator;

class MajorInfoDataValidator implements DataValidatorInterface
{
    /**
     * @var CompositeDataValidator
     */
    private $validator;

    public function __construct()
    {
        $this->validator = (new CompositeDataValidator())
            ->withValidator(new RequiredPropertiesDataValidator(['name', 'type', 'location']))
            ->withValidator(new LocationDataValidator(), ['location'])
            ->withValidator(new EventTypeDataValidator(), ['type'])
            ->withValidator(new ThemeDataValidator(), ['theme']);
    }

    /**
     * @param array $data
     * @throws DataValidationException
     */
    public function validate(array $data)
    {
        $this->validator->validate($data);
    }
}
