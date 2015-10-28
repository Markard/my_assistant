<?php namespace MyAssistant\AuthJwtBundle\Entity;


use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

/**
 * EmailConfirmation
 */
class EmailConfirmation
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $confirmationCode;

    /**
     * @var string
     */
    private $createdAt;

    /**
     * @var User
     */
    private $user;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return EmailConfirmation
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set confirmationCode
     *
     * @param string $confirmationCode
     *
     * @return EmailConfirmation
     */
    public function setConfirmationCode($confirmationCode)
    {
        $this->confirmationCode = $confirmationCode;

        return $this;
    }

    /**
     * Generate random code and set it as confirmationCode property
     *
     * @return EmailConfirmation
     */
    public function generateConfirmationCode()
    {
        $this->confirmationCode = md5(uniqid(rand(), true));

        return $this;
    }

    /**
     * Get confirmation code
     *
     * @return string
     */
    public function getConfirmationCode()
    {
        return $this->confirmationCode;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return EmailConfirmation
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * prePersist Event
     */
    public function updateTimestamps()
    {
        if (!$this->getCreatedAt()) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }

    /**
     * Validate if code expired
     *
     * @param $hoursDelta
     *
     * @return bool
     */
    public function isCodeExpired($hoursDelta)
    {
        $createdAt = Carbon::instance($this->getCreatedAt());

        return $createdAt->addHours($hoursDelta)->lt(new Carbon());
    }

    /**
     * Updates code and timestamps
     */
    public function refreshCode()
    {
        $this->generateConfirmationCode();
        $this->setCreatedAt(new \DateTime('now'));
    }

    /**
     * Number of minutes which should pass before you can resend email.
     *
     * @param int $minuteDelta
     *
     * @return bool
     */
    public function isResendTimeoutExpired($minuteDelta)
    {
        $createdAt = Carbon::instance($this->getCreatedAt());

        return $createdAt->addMinutes($minuteDelta)->lt(new Carbon());
    }

    /*
    |-------------------------------------------------------------------------------------------------------------------
    | User
    |-------------------------------------------------------------------------------------------------------------------
    */

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param User $user
     */
    public function removeUser(User $user)
    {
        $this->user = null;
    }
}
