<?php namespace MyAssistant\AuthJwtBundle\Command;


use Doctrine\Bundle\DoctrineBundle\Registry;
use MyAssistant\AuthJwtBundle\Entity\UserRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ResetIncomeCountersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('income:reset_counter')
            ->setDescription('Refresh income counter for all users.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Registry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        /** @var UserRepository $repository */
        $repository = $doctrine->getRepository('MyAssistant\AuthJwtBundle\Entity\User');

        $repository->resetIncomeCounters();

        $output->writeln("Counter successfully reset.");
    }
}