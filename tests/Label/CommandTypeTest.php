<?php

namespace CultuurNet\UDB3\Symfony\Label;

use Symfony\Component\HttpFoundation\Request;

class CommandTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_create_a_command_type_from_a_request()
    {
        $expectedCommandType = CommandType::MAKE_PRIVATE();
        $content = '{"command":"' . $expectedCommandType->toNative() . '"}';
        $request = new Request([], [], [], [], [], [], $content);

        $commandType = CommandType::createFromRequest($request);

        $this->assertEquals($expectedCommandType, $commandType);
    }

    /**
     * @test
     */
    public function it_has_a_make_visible_option()
    {
        $commandType = CommandType::MAKE_VISIBLE();

        $this->assertEquals($commandType, CommandType::MAKE_VISIBLE);
    }

    /**
     * @test
     */
    public function it_has_a_make_invisible_option()
    {
        $commandType = CommandType::MAKE_INVISIBLE();

        $this->assertEquals($commandType, CommandType::MAKE_INVISIBLE);
    }

    /**
     * @test
     */
    public function it_has_a_make_public_option()
    {
        $commandType = CommandType::MAKE_PUBLIC();

        $this->assertEquals($commandType, CommandType::MAKE_PUBLIC);
    }

    /**
     * @test
     */
    public function it_has_a_make_private_option()
    {
        $commandType = CommandType::MAKE_PRIVATE();

        $this->assertEquals($commandType, CommandType::MAKE_PRIVATE);
    }

    /**
     * @test
     */
    public function it_has_only_four_specified_options()
    {
        $options = CommandType::getConstants();

        $this->assertEquals(
            [
                CommandType::MAKE_VISIBLE()->getName() => CommandType::MAKE_VISIBLE,
                CommandType::MAKE_INVISIBLE()->getName() => CommandType::MAKE_INVISIBLE,
                CommandType::MAKE_PUBLIC()->getName() => CommandType::MAKE_PUBLIC,
                CommandType::MAKE_PRIVATE()->getName() => CommandType::MAKE_PRIVATE,
            ],
            $options
        );
    }
}
