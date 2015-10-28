<?php
namespace Helper;
// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;

class Unit extends \Codeception\Module
{
    public function getKernel()
    {
        return $this->getModule('Symfony2')->kernel;
    }
}
