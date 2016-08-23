<?php

namespace CultuurNet\UDB3\Symfony\Label\Query;

use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Query;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Number\Natural;
use ValueObjects\String\String as StringLiteral;

class QueryFactoryTest extends \PHPUnit_Framework_TestCase
{
    const QUERY_VALUE = 'label';
    const START_VALUE = 5;
    const LIMIT_VALUE = 10;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    protected function setUp()
    {
        $this->queryFactory = new QueryFactory();
    }

    /**
     * @test
     */
    public function it_can_get_query_from_request()
    {
        $request = new Request([
            QueryFactory::QUERY => self::QUERY_VALUE,
            QueryFactory::START => self::START_VALUE,
            QueryFactory::LIMIT => self::LIMIT_VALUE
        ]);

        $query = $this->queryFactory->createFromRequest($request);

        $expectedQuery = new Query(
            new StringLiteral(self::QUERY_VALUE),
            new Natural(self::START_VALUE),
            new Natural(self::LIMIT_VALUE)
        );

        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * @test
     */
    public function it_can_get_query_from_request_no_start()
    {
        $request = new Request([
            QueryFactory::QUERY => self::QUERY_VALUE,
            QueryFactory::LIMIT => self::LIMIT_VALUE
        ]);

        $query = $this->queryFactory->createFromRequest($request);

        $expectedQuery = new Query(
            new StringLiteral(self::QUERY_VALUE),
            null,
            new Natural(self::LIMIT_VALUE)
        );

        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * @test
     */
    public function it_can_get_query_from_request_no_limit()
    {
        $request = new Request([
            QueryFactory::QUERY => self::QUERY_VALUE,
            QueryFactory::START => self::START_VALUE
        ]);

        $query = $this->queryFactory->createFromRequest($request);

        $expectedQuery = new Query(
            new StringLiteral(self::QUERY_VALUE),
            new Natural(self::START_VALUE),
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
            QueryFactory::QUERY => self::QUERY_VALUE,
        ]);

        $query = $this->queryFactory->createFromRequest($request);

        $expectedQuery = new Query(
            new StringLiteral(self::QUERY_VALUE),
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
            QueryFactory::QUERY => self::QUERY_VALUE,
            QueryFactory::START => 0,
            QueryFactory::LIMIT => 0
        ]);

        $query = $this->queryFactory->createFromRequest($request);

        $expectedQuery = new Query(
            new StringLiteral(self::QUERY_VALUE),
            new Natural(0),
            new Natural(0)
        );

        $this->assertEquals($expectedQuery, $query);
    }
}
