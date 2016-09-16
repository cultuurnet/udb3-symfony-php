<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Address;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\UDB3\Address\Address;
use ValueObjects\String\String as StringLiteral;

class AddressJSONDeserializer extends JSONDeserializer
{
    /**
     * @var AddressDataValidator
     */
    private $validator;

    public function __construct()
    {
        $assoc = true;
        parent::__construct($assoc);

        $this->validator = new AddressDataValidator();
    }

    /**
     * @param StringLiteral $data
     * @return Address
     * @throws DataValidationException
     */
    public function deserialize(StringLiteral $data)
    {
        $data = parent::deserialize($data);
        $this->validator->validate($data);
        return Address::deserialize($data);
    }
}
