<?php namespace MyAssistant\AuthJwtBundle\Filters;


use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class UserFilter extends SQLFilter
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (empty($this->reader)) {
            return '';
        }

        /**
         * @see MyAssistant\AuthJwtBundle\Annotation\UserAware annotation class
         */
        $userAware = $this->reader->getClassAnnotation(
            $targetEntity->getReflectionClass(),
            'MyAssistant\AuthJwtBundle\Annotation\UserAware'
        );

        if (!$userAware) {
            return '';
        }

        $fieldName = $userAware->userFieldName;

        try {
            $userId = $this->getParameter('id');
        } catch (\InvalidArgumentException $e) {
            return '';
        }

        if (empty($fieldName) || empty($userId)) {
            return '';
        }

        return $targetTableAlias . '.' . $fieldName . '=' . $userId;
    }

    /**
     * @param Reader $reader
     */
    public function setAnnotationReader(Reader $reader)
    {
        $this->reader = $reader;
    }
}