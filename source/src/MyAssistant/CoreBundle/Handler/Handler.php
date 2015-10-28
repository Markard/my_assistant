<?php namespace MyAssistant\CoreBundle\Handler;


use MyAssistant\CoreBundle\CustomSerializer\PaginationSerializerInterface;
use MyAssistant\CoreBundle\Exception\Api\FormValidationException;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactoryInterface;

class Handler implements HandlerInterface
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var EntityRepository
     */
    protected $repository;

    protected $entityClass;

    /**
     * @var AbstractType
     */
    protected $entityType;

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * @var PaginationSerializerInterface
     */
    protected $paginationSerializer;

    /**
     * @var ContainerInterface
     */
    protected $container;


    public function __construct(
        ObjectManager $om,
        FormFactoryInterface $formFactory,
        $entityClass,
        AbstractType $entityType,
        PaginatorInterface $paginator,
        PaginationSerializerInterface $paginationSerializer,
        ContainerInterface $container
    ) {
        $this->om = $om;
        $this->entityClass = $entityClass;
        $this->repository = $om->getRepository($entityClass);
        $this->formFactory = $formFactory;
        $this->entityType = $entityType;
        $this->paginator = $paginator;
        $this->paginationSerializer = $paginationSerializer;
        $this->container = $container;
    }

    /**
     * Return entity by id
     *
     * @param $id
     *
     * @return null|object
     */
    public function get($id)
    {
        return $this->repository->find($id);
    }

    /**
     * Save new entity in database.
     *
     * @param array $parameters
     *
     * @return mixed Created entity
     * @throws FormValidationException
     */
    public function post(array $parameters)
    {
        $entity = $this->createEntity();

        $entity = $this->processForm($entity, $parameters, 'POST');
        $this->saveEntity($entity);

        return $entity;
    }

    /**
     * Create new entity object.
     *
     * @return mixed
     */
    protected function createEntity()
    {
        return new $this->entityClass();
    }

    /**
     * Validate entity with input parameters. And create or update entity.
     *
     * @param $entity
     * @param $parameters
     * @param $method
     *
     * @return mixed
     * @throws FormValidationException
     */
    protected function processForm($entity, $parameters, $method)
    {
        $form = $this->formFactory->create($this->entityType, $entity, ['method' => $method]);

        if (isset($parameters[$form->getName()])) {
            $parameters = $parameters[$form->getName()];
        }

        $form->submit($parameters, 'PATCH' !== $method);
        if ($form->isValid()) {
            $entity = $form->getData();

            return $entity;
        }

        throw new FormValidationException($form, 'Invalid submitted data');
    }

    /**
     * Save entity to database.
     *
     * @param $entity
     */
    protected function saveEntity($entity)
    {
        $this->om->persist($entity);
        $this->om->flush($entity);
    }

    /**
     * Update entity totally.
     *
     * @param $entity
     * @param array $parameters
     *
     * @return mixed
     * @throws FormValidationException
     */
    public function put($entity, array $parameters)
    {
        $entity = $this->processForm($entity, $parameters, 'PUT');

        $this->saveEntity($entity);

        return $entity;
    }

    /**
     * Update entity partially
     *
     * @param $entity
     * @param array $parameters
     *
     * @return mixed
     * @throws FormValidationException
     */
    public function patch($entity, array $parameters)
    {
        $entity = $this->processForm($entity, $parameters, 'PATCH');

        $this->saveEntity($entity);

        return $entity;
    }

    /**
     * Delete entity.
     *
     * @param $entity
     */
    public function delete($entity)
    {
        $this->om->remove($entity);
        $this->om->flush();
    }

    /**
     * Return array of entities
     * Result prepared by pagination serializer.
     * @see PaginationSerializerInterface::serialize()
     *
     * @param int $limit
     * @param int $page
     * @param null $sort
     * @param null $sortDirection
     * @param array $criteria
     *
     * @return array
     */
    public function all($limit = 10, $page = 1, $sort = null, $sortDirection = null, array $criteria = [])
    {
        $meta = $this->om->getClassMetadata($this->entityClass);
        if (!$meta->hasField($sort)) {
            $sort = $meta->getIdentifier()[0];
        }

        $query = $this->getQueryForAllItems($sort, $sortDirection, $criteria);
        $pagination = $this->paginator->paginate($query, $page, $limit);

        return $this->paginationSerializer->serialize($pagination);
    }

    /**
     * @param $sort
     * @param $sortDirection
     * @param array $criteria
     *
     * @return \Doctrine\ORM\Query
     */
    protected function getQueryForAllItems($sort, $sortDirection, array $criteria)
    {
        $queryBuilder = $this->repository->createQueryBuilder('t')
                                         ->select('t');

        foreach ($criteria as $column => $value) {
            $queryBuilder->where($column . '=:' . $column)->setParameter(':' . $column, $value);
        }

        $queryBuilder->orderBy('t.' . $sort, $sortDirection);

        return $queryBuilder->getQuery();
    }
}