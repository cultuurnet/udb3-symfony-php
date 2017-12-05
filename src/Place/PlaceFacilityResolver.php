<?php

namespace CultuurNet\UDB3\Symfony\Place;

use CultuurNet\UDB3\Facility;
use CultuurNet\UDB3\Symfony\Offer\OfferFacilityResolverInterface;
use ValueObjects\StringLiteral\StringLiteral;
 
class PlaceFacilityResolver implements OfferFacilityResolverInterface
{
    /**
     * @var Facility[]
     */
    private $facilities;

    /**
     * PlaceTypeResolver constructor.
     */
    public function __construct()
    {
        $this->facilities = [
            "3.13.1.0.0" => new Facility("3.13.1.0.0","Voorzieningen voor assistentiehonden"),
            "3.23.3.0.0" => new Facility("3.23.3.0.0","Rolstoel ter beschikking"),
            "3.25.0.0.0" => new Facility("3.25.0.0.0","Contactpunt voor personen met handicap"),
            "3.26.0.0.0" => new Facility("3.26.0.0.0","Parkeerplaats"),
        ];
    }

    /**
     * @inheritdoc
     */
    public function byId(StringLiteral $facilityId)
    {
        if (!array_key_exists((string) $facilityId, $this->facilities)) {
            throw new \Exception("Unknown place facility id '{$facilityId}'");
        }

        return $this->facilities[(string) $facilityId];
    }
}
