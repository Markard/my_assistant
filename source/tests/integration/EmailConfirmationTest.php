<?php


use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use MyAssistant\AuthJwtBundle\Entity\EmailConfirmation;
use MyAssistant\AuthJwtBundle\Entity\EmailConfirmationRepository;

class EmailConfirmationTest extends \Codeception\TestCase\Test
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /** @var  EntityManager */
    protected $em;

    /** @var  EmailConfirmationRepository */
    protected $emailConfirmationRepository;

    protected function _before()
    {
        parent::_before();
        $this->em = $this->getModule('Doctrine2')->em;
        $this->emailConfirmationRepository = $this->em->getRepository('MyAssistant\AuthJwtBundle\Entity\EmailConfirmation');
    }

    public function testGenerateCode()
    {
        $ec = (new EmailConfirmation())->generateConfirmationCode();

        $this->assertEquals(32, strlen($ec->getConfirmationCode()));
    }

    public function testIsCodeExpiredShouldReturnFalseIfCreationTimeLessThanTimeDelta()
    {
        /** @var EmailConfirmation $ec */
        $ec = $this->emailConfirmationRepository->findOneBy(['email' => 'test_user_with_confirmation@gmail.com']);

        $now = Carbon::now();
        $format = 'Y-m-d H:i:s';
        $ec->setCreatedAt(\DateTime::createFromFormat($format, $now->format($format)));

        $hourDelta = 1;
        $this->assertFalse($ec->isCodeExpired($hourDelta));
    }

    public function testIsCodeExpiredShouldReturnTrueIfCreationTimeMoreThanTimeDelta()
    {
        /** @var EmailConfirmation $ec */
        $ec = $this->emailConfirmationRepository->findOneBy(['email' => 'test_user_with_confirmation@gmail.com']);

        $now = Carbon::now();
        $format = 'Y-m-d H:i:s';
        $ec->setCreatedAt(\DateTime::createFromFormat($format, $now->subHours(2)->format($format)));

        $hourDelta = 1;
        $this->assertTrue($ec->isCodeExpired($hourDelta));
    }

    public function testIsResendLimitExceedShouldReturnFalseIfCreationTimeLessThanTimeDelta()
    {
        /** @var EmailConfirmation $ec */
        $ec = $this->emailConfirmationRepository->findOneBy(['email' => 'test_user_with_confirmation@gmail.com']);

        $now = Carbon::now();
        $format = 'Y-m-d H:i:s';
        $ec->setCreatedAt(\DateTime::createFromFormat($format, $now->format($format)));

        $minuteDelta = 10;
        $this->assertFalse($ec->isResendTimeoutExpired($minuteDelta));
    }

    public function testIsResendLimitExceedShouldReturnTrueIfCreationTimeMoreThanTimeDelta()
    {
        /** @var EmailConfirmation $ec */
        $ec = $this->emailConfirmationRepository->findOneBy(['email' => 'test_user_with_confirmation@gmail.com']);

        $now = Carbon::now();
        $format = 'Y-m-d H:i:s';
        $ec->setCreatedAt(\DateTime::createFromFormat($format, $now->subMinutes(11)->format($format)));

        $minuteDelta = 10;
        $this->assertTrue($ec->isResendTimeoutExpired($minuteDelta));
    }
}