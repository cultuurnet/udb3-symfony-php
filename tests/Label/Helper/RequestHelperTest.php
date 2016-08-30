<?php

namespace CultuurNet\UDB3\Symfony\Label\Helper;

use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Query;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Number\Natural;
use ValueObjects\String\String as StringLiteral;

class RequestHelperTest extends \PHPUnit_Framework_TestCase
{
    const LABEL_NAME = 'labelName';

    const QUERY = 'label';
    const START = 5;
    const LIMIT = 10;

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

        $query = [
            'query' => self::QUERY,
            'start' => self::START,
            'limit' => self::LIMIT
        ];

        $content = json_encode([
            'name' => self::LABEL_NAME,
            'visibility' => 'invisible',
            'privacy' => 'private',
            'command' => 'MakeVisible'
        ]);

        $this->request = new Request($query, [], [], [], [], [], $content);
    }

    /**
     * @test
     */
    public function it_can_get_name_from_request()
    {
        $labelName = $this->requestHelper->getName($this->request);

        $this->assertEquals(
            new LabelName(self::LABEL_NAME),
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

    /**
     * @test
     */
    public function it_can_get_query_from_request()
    {
        $query = $this->requestHelper->getQuery($this->request);

        $expectedQuery = new Query(
            new StringLiteral(self::QUERY),
            new Natural(self::START),
            new Natural(self::LIMIT)
        );

        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * @test
     */
    public function it_can_get_query_from_request_no_start()
    {
        $request = new Request([
            'query' => self::QUERY,
            'limit' => self::LIMIT
        ]);

        $query = $this->requestHelper->getQuery($request);

        $expectedQuery = new Query(
            new StringLiteral(self::QUERY),
            null,
            new Natural(self::LIMIT)
        );

        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * @test
     */
    public function it_can_get_query_from_request_no_limit()
    {
        $request = new Request([
            'query' => self::QUERY,
            'start' => self::START
        ]);

        $query = $this->requestHelper->getQuery($request);

        $expectedQuery = new Query(
            new StringLiteral(self::QUERY),
            new Natural(self::START),
            null
        );

        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * @test
     */
    public function it_can_get_query_from_request_no_start_and_no_limit()
    {
        $request = new Request([
            'query' => self::QUERY,
        ]);

        $query = $this->requestHelper->getQuery($request);

        $expectedQuery = new Query(
            new StringLiteral(self::QUERY),
            null,
            null
        );

        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * @test
     */
    public function it_can_get_query_from_request_with_zero_start_and_zero_limit()
    {
        $request = new Request([
            'query' => self::QUERY,
            'start' => 0,
            'limit' => 0
        ]);

        $query = $this->requestHelper->getQuery($request);

        $expectedQuery = new Query(
            new StringLiteral(self::QUERY),
            new Natural(0),
            new Natural(0)
        );

        $this->assertEquals($expectedQuery, $query);
    }
}
