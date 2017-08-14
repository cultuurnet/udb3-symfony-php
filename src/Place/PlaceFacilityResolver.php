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
            "3.23.1.0.0" => new Facility("3.23.1.0.0", "Voorzieningen voor rolstoelgebruikers"),
            "3.23.2.0.0" => new Facility("3.23.2.0.0", "Assistentie"),
            "3.23.3.0.0" => new Facility("3.23.3.0.0", "Rolstoel ter beschikking"),
            "3.13.1.0.0" => new Facility("3.13.1.0.0", "Voorzieningen voor hulp- en/of geleidehonden"),
            "3.13.2.0.0" => new Facility("3.13.2.0.0", "Audiodescriptie"),
            "3.13.3.0.0" => new Facility("3.13.3.0.0", "Brochure beschikbaar in braille"),
            "3.13.4.0.0" => new Facility("3.13.4.0.0", "Brochure beschikbaar in grootletterschrift"),
            "3.13.5.0.0" => new Facility("3.13.5.0.0", "Brochure beschikbaar in gesproken vorm"),
            "3.17.1.0.0" => new Facility("3.17.1.0.0", "Ringleiding"),
            "3.17.2.0.0" => new Facility("3.17.2.0.0", "Voelstoelen"),
            "3.17.3.0.0" => new Facility("3.17.3.0.0", "Ondertiteling"),
        ];
    }
 
    public function byId(StringLiteral $facilityId)
    {
        if (!array_key_exists((string) $facilityId, $this->facilities)) {
            throw new \Exception("Unknown place facility id '{$facilityId}'");
        }

        return $this->facilities[(string) $facilityId];
    }
}
