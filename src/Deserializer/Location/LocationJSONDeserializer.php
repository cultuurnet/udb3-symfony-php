<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Location;

use CultuurNet\Deserializer\DataValidationException;
use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\UDB3\Location\Location;
use CultuurNet\UDB3\Symfony\Deserializer\Address\AddressJSONDeserializer;
use ValueObjects\String\String as StringLiteral;

class LocationJSONDeserializer extends JSONDeserializer
{
    /**
     * @var AddressJSONDeserializer
     */
    private $addressDeserializer;

    public function __construct()
    {
        $assoc = true;
        parent::__construct($assoc);

        $this->addressDeserializer = new AddressJSONDeserializer();
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

        $errors = [];
        $requiredArguments = ['id', 'name', 'address'];

        foreach ($requiredArguments as $requiredArgument) {
            if (!isset($data[$requiredArgument])) {
                $errors[] = "{$requiredArgument} is required but could not be found.";
            } elseif (empty($data[$requiredArgument])) {
                $errors[] = "{$requiredArgument} should not be empty.";
            }
        }

        // @todo Find a better way to recursively validate json data.
        if (!empty($data['address'])) {
            try {
                $this->addressDeserializer->deserialize(
                    new StringLiteral(json_encode($data['address']))
                );
            } catch (DataValidationException $e) {
                foreach ($e->getValidationMessages() as $validationMessage) {
                    $errors[] = 'address.' . $validationMessage;
                }
            }
        }

        if (!empty($errors)) {
            $validationException = new DataValidationException();
            $validationException->setValidationMessages($errors);
            throw $validationException;
        }

        $data['cdbid'] = $data['id'];
        unset($data['id']);

        return Location::deserialize($data);
    }
}
