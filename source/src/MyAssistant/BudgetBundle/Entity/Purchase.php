<?php namespace MyAssistant\BudgetBundle\Entity;


use MyAssistant\AuthJwtBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use MyAssistant\AuthJwtBundle\Annotation\UserAware;

/**
 * Purchase
 *
 * @UserAware(userFieldName="user_id")
 */
class Purchase
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var integer
     */
    private $amount;

    /**
     * @var string
     */
    private $price;

    /**
     * @var \DateTime
     */
    private $boughtAt;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * Virtual property
     * @var string
     */
    private $locale = 'en';

    /**
     * @var User
     */
    private $user;

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
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
     * Set title
     *
     * @param string $title
     *
     * @return Purchase
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     *
     * @return Purchase
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return Purchase
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set boughtAt
     *
     * @param \DateTime $boughtAt
     *
     * @return Purchase
     */
    public function setBoughtAt(\DateTime $boughtAt = null)
    {
        $this->boughtAt = $boughtAt;

        return $this;
    }

    /**
     * Get boughtAt
     *
     * @return \DateTime
     */
    public function getBoughtAt()
    {
        return $this->boughtAt;
    }

    /**
     * Get boughtAt
     *
     * @return string
     */
    public function getBoughtAtAsDay()
    {
        return $this->boughtAt->format('Y-m-d');
    }

    /**
     * Get boughtAt
     *
     * @return string
     */
    public function getBoughtAtAsTimestamp()
    {
        return $this->boughtAt->format('U');
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
     * -----------------------------------------------------------------------------------------------------------------
     * Events
     * -----------------------------------------------------------------------------------------------------------------
     */

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
     * prePersist Event
     */
    public function increaseUserPurchasesCounter()
    {
        $user = $this->getUser();
        $user->setPurchasesPerDay($user->getPurchasesPerDay() + 1);
    }

    /**
     * preRemove Event
     */
    public function decreaseUserPurchasesCounter()
    {
        $user = $this->getUser();
        $user->setPurchasesPerDay($user->getPurchasesPerDay() - 1);
    }

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * User
     * -----------------------------------------------------------------------------------------------------------------
     */

    public function setUser(User $user)
    {
        if (!$this->user || !$this->user->isEqualTo($user)) {
            $this->user = $user;
            $this->user->addPurchase($this);
        }

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function removeUser()
    {
        $this->user = null;
    }
}
