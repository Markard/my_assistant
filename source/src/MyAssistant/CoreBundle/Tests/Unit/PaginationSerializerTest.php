<?php namespace MyAssistant\CoreBundle\Tests\Unit;


use MyAssistant\CoreBundle\CustomSerializer\PaginationSerializer;
use Knp\Component\Pager\Paginator;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class PaginationSerializerTest extends WebTestCase
{
    public function testSerialize()
    {
        $arrayForTests = [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => 4],
            ['id' => 5],
        ];
        $currentPage = 1;
        $limit = 3;

        $sPaginator = (new Paginator())->paginate($arrayForTests, $currentPage, $limit);

        $serializer = new PaginationSerializer();
        $this->assertEquals([
            'page' => 1,
            'num_items_per_page' => 3,
            'total_count' => 5,
            'items' => [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
            ]
        ], $serializer->serialize($sPaginator));
    }
}