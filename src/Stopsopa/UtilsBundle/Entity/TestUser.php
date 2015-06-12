<?php

namespace Stopsopa\UtilsBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * TestUser
 */
class TestUser extends AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $surname;

    /**
     */
    protected $comments;

    public function __construct() {
        $this->comments = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return TestUser
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set surname
     *
     * @param string $surname
     * @return TestUser
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    public function addTestComment(TestComment $testcomment) {
        $testcomment->setUser($this);
        $this->comments->add($testcomment);
        return $this;
    }
    public function removeTestComment(TestComment $testcomment) {
        $testcomment->setUser(null);
        $this->comments->removeElement($testcomment);
        return $this;
    }
    /**
     * @return ArrayCollection
     */
    public function getComments() {
       return $this->comments;
    }

    public function setComments($comments) {

        if ( ! ($comments instanceof ArrayCollection) && !is_array($comments) )
            $comments = array($comments);

        if (is_array($comments))
            $comments = new ArrayCollection($comments);

        $this->comments = $comments;

        return $this;
    }
}
