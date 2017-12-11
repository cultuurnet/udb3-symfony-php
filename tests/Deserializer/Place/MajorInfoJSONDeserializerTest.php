<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Place;

use ValueObjects\StringLiteral\StringLiteral;

class MajorInfoJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_serialize_major_info_with_english_name()
    {
        $majorInfoAsJson = file_get_contents(__DIR__ . '/../samples/place-major-info-with-english-name.json');

        $majorInfoJSONDeserializer = new MajorInfoJSONDeserializer();

        $majorInfo = $majorInfoJSONDeserializer->deserialize(new StringLiteral($majorInfoAsJson));

        $this->assertEquals($majorInfo->getTitle(), 'Test plaats');
    }
}
