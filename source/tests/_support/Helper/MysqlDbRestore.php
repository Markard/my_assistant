<?php namespace Helper;


use Codeception\Configuration;
use Codeception\Exception\ModuleConfigException;
use Codeception\TestCase;

class MysqlDbRestore extends \Codeception\Module
{
    protected $isDatabaseChange = false;

    /**
     * @var array
     */
    protected $config = [
        'host' => 'localhost',
        'user' => '',
        'password' => '',
        'database' => null,
        'dump' => null,
        'populate' => true,
        'cleanup' => true,
    ];

    /**
     * @var bool
     */
    protected $populated = false;

    /**
     * @var array
     */
    protected $requiredFields = ['host', 'database', 'dump'];

    public function _initialize()
    {
        if (!$this->config['dump'] || !file_exists(Configuration::projectDir() . $this->config['dump'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "\nFile with dump doesn't exist.\n"
                . "Please, check path for sql file: "
                . $this->config['dump']
            );
        }

        if ($this->config['populate']) {
            $this->loadDump();
            $this->populated = true;
        }
    }

    public function _before(TestCase $test)
    {
        if (($this->config['cleanup'] && !$this->populated) || $this->isDatabaseChange) {
            $this->loadDump();
            $this->isDatabaseChange = false;
        }
        parent::_before($test);
    }

    public function _after(TestCase $test)
    {
        $this->populated = false;
        parent::_after($test);
    }

    /**
     * Mark that database changed.
     * Should be used on test start.
     */
    public function expectDatabaseChange()
    {
        $this->isDatabaseChange = true;
    }

    public function loadDump()
    {
        $command = '';
        if ($password = $this->config['password']) {
            $command .= "MYSQL_PWD={$password} ";
        }

        $command .= 'mysql ';
        if ($host = $this->config['host']) {
            $command .= "-h{$host} ";
        }
        if ($user = $this->config['user']) {
            $command .= "-u{$user} ";
        }
        $command .= "{$this->config['database']} < " . Configuration::projectDir() . $this->config['dump'];

        exec($command);
    }

}