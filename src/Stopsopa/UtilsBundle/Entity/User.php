<?php

namespace Stopsopa\UtilsBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Stopsopa\UtilsBundle\Lib\Standalone\Urlizer;
use Stopsopa\UtilsBundle\Lib\Standalone\UtilFilesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 */
class User extends AbstractEntity
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
     * reutrn ArrayCollection
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
     * @return User
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
     * @return User
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
    public function addComment(Comment $comment) {
        $comment->setUser($this);
        $this->comments->add($comment);

        return $this;
    }
    public function removeComment(Comment $comment) {
        $comment->setUser(null);
        $this->comments->removeElement($comment);
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


    public $file;
    /**
     * W tym polu faktycznie będzie trzymany fragment ścieżki do pliku
     * @var string
     */
    public $path;
    public function setPath($path) {
        $this->path = $path;
        return $this;
    }
    function getPath() {
        return $this->path;
    }





    public function getWebPath() {
        return '/bundles/stopsopautils/utils/lorem.jpg';
    }
}
