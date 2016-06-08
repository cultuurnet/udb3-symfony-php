<?php

namespace CultuurNet\UDB3\Symfony\Label\Helper;

use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\String\String as StringLiteral;

class RequestHelperTest extends \PHPUnit_Framework_TestCase
{
    const LABEL_NAME = 'labelName';

    /**
     * @var RequestHelper
     */
    private $requestHelper;

    /**
     * @var Request
     */
    private $request;

    protected function setUp()
    {
        $this->requestHelper = new RequestHelper();

        $content = json_encode([
            'name' => self::LABEL_NAME,
            'visibility' => 'invisible',
            'privacy' => 'private',
            'command' => 'makeVisible'
        ]);

        $this->request = new Request([], [], [], [], [], [], $content);
    }

    /**
     * @test
     */
    public function it_can_get_name_from_request()
    {
        $labelName = $this->requestHelper->getName($this->request);
        $this->assertEquals(
            new StringLiteral(self::LABEL_NAME),
            $labelName
        );
    }

    /**
     * @test
     */
    public function it_can_get_visibility_from_request()
    {
        $visibility = $this->requestHelper->getVisibility($this->request);
        $this->assertEquals(
            Visibility::INVISIBLE(),
            $visibility
        );
    }

    /**
     * @test
     */
    public function it_can_get_privacy_from_request()
    {
        $privacy = $this->requestHelper->getPrivacy($this->request);
        $this->assertEquals(
            Privacy::PRIVACY_PRIVATE(),
            $privacy
        );
    }

    /**
     * @test
     */
    public function it_can_get_command_from_request()
    {
        $command = $this->requestHelper->getCommandType($this->request);
        $this->assertEquals(
            CommandType::MAKE_VISIBLE(),
            $command
        );
    }
}
