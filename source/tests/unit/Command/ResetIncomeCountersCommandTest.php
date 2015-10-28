<?php namespace Tests\unit\Command;


use MyAssistant\AuthJwtBundle\Command\ResetIncomeCountersCommand;
use MyAssistant\AuthJwtBundle\Command\ResetPurchaseCountersCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\Kernel;
use Tests\unit\BaseTest;
use Mockery as m;

class ResetIncomeCounterCommandTest extends BaseTest
{
    public function testCommand()
    {
        /**
         * Mocks
         */
        $repositoryMock = m::mock('MyAssistant\AuthJwtBundle\Entity\UserRepository');
        $repositoryMock->shouldReceive('resetIncomeCounters')
               ->withNoArgs()
                ->once();

        $doctrineMock = m::mock('Doctrine\Bundle\DoctrineBundle\Registry');
        $doctrineMock->shouldReceive('getRepository')
            ->with('MyAssistant\AuthJwtBundle\Entity\User')
            ->once()
            ->andReturn($repositoryMock);

        /** @var Kernel $kernel */
        $kernel = $this->tester->getKernel();
        $kernel->getContainer()->set('doctrine', $doctrineMock);
        $application = new Application($kernel);
        $application->add(new ResetIncomeCountersCommand());

        /** @var ResetIncomeCountersCommand $command */
        $command = $application->find('income:reset_counter');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        /**
         * Assertions
         */
        $this->tester->assertEquals("Counter successfully reset.\n", $commandTester->getDisplay());
    }
}