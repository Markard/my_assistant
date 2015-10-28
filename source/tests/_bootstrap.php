<?php

use Doctrine\Bundle\MigrationsBundle\Command\MigrationsMigrateDoctrineCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\Console\Output\ConsoleOutput;

const MIGRATIONS_HASH_FILE_NAME = 'migrations_hash';
const DUMP_FILE_NAME = 'dump.sql';

/*
 * ---------------------------------------------------------------------------------------------------------------------
 * Initialize application
 * ---------------------------------------------------------------------------------------------------------------------
 */

require_once __DIR__ . '/../app/bootstrap.php.cache';;
require_once __DIR__ . '/../app/AppKernel.php';

$kernel = new AppKernel('test', true); // create a "test" kernel
$kernel->boot();

/*
 * ---------------------------------------------------------------------------------------------------------------------
 * Generate change hash from migrations files change dates.
 * ---------------------------------------------------------------------------------------------------------------------
 */

$config = Codeception\Configuration::config(__DIR__ . '/../codeception.yml');

//Get change dates from migrations

if (!isset($config['paths']) || !isset($config['paths']['migrations'])) {
    throw new Exception('You have to define path for your migrations in codecept.yml file. Field name: migrations');
}

$migrationsPath = $config['paths']['migrations'];
if (!is_dir($migrationsPath)) {
    throw new Exception("Migrations dir doesn't exist. Please check your migrations configuration in codecept.yml");
}

$di = new RecursiveDirectoryIterator($migrationsPath,
    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME);
$i = new RecursiveIteratorIterator($di);
$ri = new RegexIterator($i, '/^.*\.php$/');

$changeDates = [];
/** @var SplFileInfo $migrationsFile */
foreach ($ri as $fileName => $migrationsFile) {
    $changeDates[$fileName] = $migrationsFile->getMTime();
}

//Get change dates from fixtures

$paths = array();
foreach ($kernel->getBundles() as $bundle) {
    $fixturePath = $bundle->getPath().'/DataFixtures/ORM';
    if (!is_dir($fixturePath)) {
        continue;
    }

    $di = new RecursiveDirectoryIterator($fixturePath,
        FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME);
    $i = new RecursiveIteratorIterator($di);
    $ri = new RegexIterator($i, '/^.*\.php$/');

    foreach ($ri as $fileName => $fixtureFile) {
        $changeDates[$fileName] = $fixtureFile->getMTime();
    }
}

$changeHash = md5(serialize($changeDates));

/*
 * ---------------------------------------------------------------------------------------------------------------------
 * Check that hash is same and dump exists.
 * ---------------------------------------------------------------------------------------------------------------------
 */

$tmpFolderPath = $config['paths']['log'];
$dataFolderPath = $config['paths']['data'];

$migrationsHashFilePath = $tmpFolderPath . DIRECTORY_SEPARATOR . MIGRATIONS_HASH_FILE_NAME;
$dumpFilePath = $dataFolderPath . DIRECTORY_SEPARATOR . DUMP_FILE_NAME;
if (is_file($migrationsHashFilePath) && file_get_contents($migrationsHashFilePath) === $changeHash && is_file($dumpFilePath)) {
    return;
}


$application = new Application($kernel);
$output = new ConsoleOutput();
/*
 * ---------------------------------------------------------------------------------------------------------------------
 * Drop database
 * ---------------------------------------------------------------------------------------------------------------------
 */
$command = new \Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand();
$application->add($command);
$input = new ArrayInput([
    'command' => 'doctrine:database:drop',
    '--force' => true,
    '--env' => 'test'
]);
$command->run($input, $output);

/*
 * ---------------------------------------------------------------------------------------------------------------------
 * Create database
 * ---------------------------------------------------------------------------------------------------------------------
 */
$command = new \Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand();
$application->add($command);
$input = new ArrayInput([
    'command' => 'doctrine:database:create',
    '--env' => 'test'
]);
$command->run($input, $output);

/*
 * ---------------------------------------------------------------------------------------------------------------------
 * Generate sql scheme dump to dump file.
 * ---------------------------------------------------------------------------------------------------------------------
 */
$command = new MigrationsMigrateDoctrineCommand();
$application->add($command);
$input = new ArrayInput([
    'command' => 'doctrine:migrations:migrate',
    '--no-interaction' => true,
    '--quiet' => true,
    '--env' => 'test'
]);
$command->run($input, $output);

/*
 * ---------------------------------------------------------------------------------------------------------------------
 * Upload fixtures
 * ---------------------------------------------------------------------------------------------------------------------
 */
$command = new \Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand();
$application->add($command);
$input = new ArrayInput([
    'command' => 'doctrine:fixtures:load',
    '--env' => 'test'
]);
$command->run($input, $output);

/**
 *
 * Generate sql dump
 *
 */
preg_match('/host=([^;]+);/', $config['modules']['config']['\Helper\CustomDb']['dsn'], $matches);
$host = $matches[1];
preg_match('/dbname=([^;]+);/', $config['modules']['config']['\Helper\CustomDb']['dsn'], $matches);
$dbname = $matches[1];
$dbUser = $config['modules']['config']['\Helper\CustomDb']['user'];
$dbPassword = $config['modules']['config']['\Helper\CustomDb']['password'];
exec("mysqldump -h{$host} -u{$dbUser} {$dbname} > " . $dumpFilePath);

/*
 * ---------------------------------------------------------------------------------------------------------------------
 * Update hash file
 * ---------------------------------------------------------------------------------------------------------------------
 */

file_put_contents($migrationsHashFilePath, $changeHash);

