<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Place;

use CultuurNet\UDB3\Symfony\Deserializer\DataValidator\CompositeDataValidator;
use CultuurNet\UDB3\Symfony\Deserializer\DataValidator\DataValidatorInterface;
use CultuurNet\UDB3\Symfony\Deserializer\DataValidator\RequiredPropertiesDataValidator;

class CreatePlaceDataValidator implements DataValidatorInterface
{
    /**
     * @var CompositeDataValidator
     */
    private $validator;

    public function __construct()
    {
        $this->validator = (new CompositeDataValidator())
            ->withValidator(new MajorInfoDataValidator())
            ->withValidator(new RequiredPropertiesDataValidator(['mainLanguage']));
    }

    /**
     * @inheritdoc
     */
    public function validate(array $data)
    {
        $this->validator->validate($data);
    }
}
