<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Organizer;

use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\Deserializer\MissingValueException;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class WebsiteJSONDeserializer extends JSONDeserializer
{
    /**
     * @param StringLiteral $data
     * @return Url
     */
    public function deserialize(StringLiteral $data)
    {
        $data = parent::deserialize($data);

        if (!isset($data->website)) {
            throw new MissingValueException('Missing value for "website".');
        }

        return Url::fromNative($data->website);
    }
}
