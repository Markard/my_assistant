<?php namespace MyAssistant\AuthJwtBundle\Entity;


use MyAssistant\BudgetBundle\Entity\Income;
use MyAssistant\BudgetBundle\Entity\Purchase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 */
class User implements UserInterface, EquatableInterface, EncoderAwareInterface
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $timezone = 'UTC';

    /**
     * @var integer
     */
    private $purchasesPerDay = 0;

    /**
     * @var integer
     */
    private $incomesPerMonth = 0;

    /**
     * @var string
     */
    private $createdAt;

    /**
     * @var string
     */
    private $updatedAt;

    /**
     * @var ArrayCollection
     */
    private $purchases;

    /**
     * @var ArrayCollection
     */
    private $incomes;

    /**
     * @var EmailConfirmation
     */
    private $emailConfirmation;

    public function __construct()
    {
        $this->purchases = new ArrayCollection();
        $this->incomes = new ArrayCollection();
    }

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
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
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
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set timezone
     *
     * @param string $timezone
     *
     * @return User
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get timezone
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Return purchases made for current day.
     *
     * @return integer
     */
    public function getPurchasesPerDay()
    {
        return $this->purchasesPerDay;
    }

    /**
     * Set purchases per day for current user.
     *
     * @param integer $purchasesPerDay
     */
    public function setPurchasesPerDay($purchasesPerDay)
    {
        $this->purchasesPerDay = $purchasesPerDay;
    }

    /**
     * Return incomes made for current month.
     *
     * @return integer
     */
    public function getIncomesPerMonth()
    {
        return $this->incomesPerMonth;
    }

    /**
     * Set income per day for current user.
     *
     * @param integer $incomesPerMonth
     */
    public function setIncomesPerMonth($incomesPerMonth)
    {
        $this->incomesPerMonth = $incomesPerMonth;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Purchase
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Purchase
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Check is user persisted in database.
     *
     * @return bool
     */
    public function isNew()
    {
        return !(bool)$this->id;
    }

    /**
     * prePersist Event
     */
    public function updateTimestamps()
    {
        $this->setUpdatedAt(new \DateTime('now'));

        if (!$this->getCreatedAt()) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Email Confirmations
     * -----------------------------------------------------------------------------------------------------------------
     */

    /**
     * @return EmailConfirmation
     */
    public function getEmailConfirmation()
    {
        return $this->emailConfirmation;
    }

    /**
     * @param EmailConfirmation $emailConfirmation
     */
    public function setEmailConfirmation(EmailConfirmation $emailConfirmation)
    {
        $this->emailConfirmation = $emailConfirmation;
        $emailConfirmation->setUser($this);
        $emailConfirmation->setEmail($this->getEmail());
    }

    public function removeEmailConfirmation()
    {
        $this->emailConfirmation->setEmail(null);
        $this->emailConfirmation = null;
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Purchases
     * -----------------------------------------------------------------------------------------------------------------
     */

    /**
     * @return ArrayCollection
     */
    public function getPurchase()
    {
        return $this->purchases;
    }

    public function addPurchase(Purchase $purchase)
    {
        if (!$this->purchases->contains($purchase)) {
            $this->purchases->add($purchase);
            $purchase->setUser($this);
        }

        return $this;
    }

    public function removePurchase(Purchase $purchase)
    {
        if ($this->purchases->contains($purchase)) {
            $this->purchases->removeElement($purchase);
        }

        return $this;
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Incomes
     * -----------------------------------------------------------------------------------------------------------------
     */

    /**
     * @return ArrayCollection
     */
    public function getIncome()
    {
        return $this->incomes;
    }

    public function addIncome(Income $income)
    {
        if (!$this->incomes->contains($income)) {
            $this->incomes->add($income);
            $income->setUser($this);
        }

        return $this;
    }

    public function removeIncome(Income $income)
    {
        if ($this->incomes->contains($income)) {
            $this->incomes->removeElement($income);
        }

        return $this;
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Implement UserInterface
     * -----------------------------------------------------------------------------------------------------------------
     */

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {

    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Implement EquatableInterface
     * -----------------------------------------------------------------------------------------------------------------
     */

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User
            || $this->password !== $user->getPassword()
            || $this->username !== $user->getUsername()
            || $this->email !== $user->getEmail()
        ) {
            return false;
        }

        return true;
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Implement EncoderAwareInterface
     * -----------------------------------------------------------------------------------------------------------------
     */

    /**
     * {@inheritdoc}
     */
    public function getEncoderName()
    {
        return 'default_encoder';
    }
}
