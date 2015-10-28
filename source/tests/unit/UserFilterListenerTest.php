<?php


use Mockery as m;
use Tests\unit\BaseTest;

class UserFilterListenerTest extends BaseTest
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testEventHandling()
    {
        /**
         * Mocks
         */
        $userMock = m::mock('MyAssistant\AuthJwtBundle\Entity\User');
        $userMock
            ->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn(1);

        $tokenMock = m::mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $tokenMock
            ->shouldReceive('getUser')
            ->once()
            ->withNoArgs()
            ->andReturn($userMock);

        $tokenStorageMock = m::mock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $tokenStorageMock
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($tokenMock);

        $readerMock = m::mock('Doctrine\Common\Annotations\Reader');

        $userFilter = m::mock();
        $userFilter
            ->shouldReceive('setParameter')
            ->once()
            ->withArgs(['id', 1]);
        $userFilter
            ->shouldReceive('setAnnotationReader')
            ->once()
            ->with($readerMock);

        $filterCollectionMock = m::mock('Doctrine\ORM\Query\FilterCollection');
        $filterCollectionMock
            ->shouldReceive('enable')
            ->with('user_filter')
            ->once()
            ->andReturn($userFilter);

        $emMock = m::mock('Doctrine\ORM\EntityManager');
        $emMock
            ->shouldReceive('getFilters')
            ->once()
            ->andReturn($filterCollectionMock);

        $listener = new \MyAssistant\AuthJwtBundle\EventListener\UserFilterListener($emMock, $tokenStorageMock, $readerMock);

        /**
         * Assertions
         */
        $listener->onKernelRequest();
    }
}