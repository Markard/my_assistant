<?php namespace MyAssistant\CoreBundle\CustomSerializer;

use Knp\Component\Pager\Pagination\PaginationInterface;

class PaginationSerializer implements PaginationSerializerInterface
{
    public function serialize(PaginationInterface $pagination)
    {
        return [
            'page' => $pagination->getCurrentPageNumber(),
            'num_items_per_page' => $pagination->getItemNumberPerPage(),
            'total_count' => $pagination->getTotalItemCount(),
            'items' => $pagination->getItems()
        ];
    }
}