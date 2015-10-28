<?php namespace MyAssistant\BudgetBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use MyAssistant\AuthJwtBundle\Entity\User;
use MyAssistant\AuthJwtBundle\Annotation\UserAware;

/**
 * Income
 *
 * @UserAware(userFieldName="user_id")
 */
class Income
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
     * @var string
     */
    private $price;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

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
     * Set title
     *
     * @param string $title
     * @return Income
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
     * Set price
     *
     * @param string $price
     * @return Income
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
     * Set date
     *
     * @param \DateTime $date
     * @return Income
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Income
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
     * @return Income
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
     * Set userId
     *
     * @param integer $userId
     * @return Income
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
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
    public function increaseUserIncomesCounter()
    {
        $user = $this->getUser();
        $user->setIncomesPerMonth($user->getIncomesPerMonth() + 1);
    }

    /**
     * preRemove Event
     */
    public function decreaseUserIncomesCounter()
    {
        $user = $this->getUser();
        $user->setIncomesPerMonth($user->getIncomesPerMonth() - 1);
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
            $this->user->addIncome($this);
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
