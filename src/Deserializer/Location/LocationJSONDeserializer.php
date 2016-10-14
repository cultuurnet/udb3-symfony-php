<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Location;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\UDB3\Location\Location;
use ValueObjects\String\String as StringLiteral;

class LocationJSONDeserializer extends JSONDeserializer
{
    /**
     * @var LocationDataValidator
     */
    private $validator;

    public function __construct()
    {
        $assoc = true;
        parent::__construct($assoc);

        $this->validator = new LocationDataValidator();
    }

    /**
     * @param StringLiteral $data
     *
     * @return Location
     *
     * @throws DataValidationException
     */
    public function deserialize(StringLiteral $data)
    {
        $data = parent::deserialize($data);

        $this->validator->validate($data);

        $data['cdbid'] = $data['id'];
        unset($data['id']);

        return Location::deserialize($data);
    }
}
