<?php

namespace CultuurNet\UDB3\Symfony\Label\Helper;

use ValueObjects\Enum\Enum;

/**
 * Class CommandType
 * @package CultuurNet\UDB3\Symfony\Label\Helper
 * @method static CommandType MAKE_VISIBLE
 * @method static CommandType MAKE_INVISIBLE
 * @method static CommandType MAKE_PUBLIC
 * @method static CommandType MAKE_PRIVATE
 */
class CommandType extends Enum
{
    const MAKE_VISIBLE = 'makeVisible';
    const MAKE_INVISIBLE = 'makeInvisible';
    const MAKE_PUBLIC = 'makePublic';
    const MAKE_PRIVATE = 'makePrivate';
}
