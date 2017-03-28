<?php

namespace CultuurNet\UDB3\Symfony\Deserializer\Organizer;

use CultuurNet\Deserializer\MissingValueException;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class WebsiteJSONDeserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WebsiteJSONDeserializer
     */
    private $websiteJSONDeserializer;

    protected function setUp()
    {
        $this->websiteJSONDeserializer = new WebsiteJSONDeserializer();
    }

    /**
     * @test
     */
    public function it_can_serialize_a_valid_website()
    {
        $json = new StringLiteral('{"website":"http://www.depot.be"}');

        $actual = $this->websiteJSONDeserializer->deserialize($json);

        $this->assertEquals(
            Url::fromNative('http://www.depot.be'),
            $actual
        );
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_website_is_missing()
    {
        $json = new StringLiteral('{"url":"http://www.depot.be"}');

        $this->expectException(MissingValueException::class);
        $this->expectExceptionMessage('Missing value for "website".');

        $this->websiteJSONDeserializer->deserialize($json);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_website_is_invalid()
    {
        $json = new StringLiteral('{"website":"http:/www.depot.be"}');

        $this->expectException(\InvalidArgumentException::class);

        $this->websiteJSONDeserializer->deserialize($json);
    }
}
