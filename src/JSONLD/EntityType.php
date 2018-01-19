<?php

namespace CultuurNet\UDB3\Symfony\JSONLD;

use ValueObjects\Enum\Enum;

/**
 * @method static EntityType EVENT()
 * @method static EntityType PLACE()
 * @method static EntityType ORGANIZER()
 * @method static EntityType POSTAL_ADDRESS()
 * @method static EntityType BOOKING_INFO()
 * @method static EntityType CONTACT_POINT()
 * @method static EntityType PRICE_INFO()
 * @method static EntityType TAXONOMY_TERM()
 * @method static EntityType ENTRY_POINT()
 */
class EntityType extends Enum
{
    const EVENT = 'Event';
    const PLACE = 'Place';
    const ORGANIZER = 'Organizer';
    const POSTAL_ADDRESS = 'PostalAddress';
    const BOOKING_INFO = 'BookingInfo';
    const CONTACT_POINT = 'ContactPoint';
    const PRICE_INFO = 'PriceInfo';
    const TAXONOMY_TERM = 'TaxonomyTerm';
    const ENTRY_POINT = 'EntryPoint';
}
