<?php namespace MyAssistant\CoreBundle\CustomSerializer;

use Knp\Component\Pager\Pagination\PaginationInterface;

interface PaginationSerializerInterface
{
    public function serialize(PaginationInterface $pagination);
}