<?php

namespace CultuurNet\UDB3\Symfony\Place;

use CultuurNet\UDB3\Facility;
use ValueObjects\StringLiteral\StringLiteral;

class PlaceFacilityResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_not_resolve_a_facility_when_the_id_is_unknown()
    {
        $resolver = new PlaceFacilityResolver();

        $this->expectExceptionMessage("Unknown place facility id '1.8.2'");

        $resolver->byId(new StringLiteral('1.8.2'));
    }

    /**
     * @test
     */
    public function it_should_return_the_matching_facility_when_passed_a_known_id()
    {
        $resolver = new PlaceFacilityResolver();

        $facility = $resolver->byId(new StringLiteral('3.23.2.0.0'));
        $expectedFacility = new Facility("3.23.2.0.0", "Assistentie");

        $this->assertEquals($expectedFacility, $facility);
    }
}
