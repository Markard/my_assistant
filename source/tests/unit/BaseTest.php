<?php namespace Tests\unit;


use Codeception\TestCase\Test;
use Mockery as m;

class BaseTest extends Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _after()
    {
        m::close();
    }
}