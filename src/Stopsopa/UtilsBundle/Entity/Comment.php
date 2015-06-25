<?php

namespace Stopsopa\UtilsBundle\Entity;

use DateTime;
use Stopsopa\UtilsBundle\Lib\Standalone\UtilFilesystem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Stopsopa\UtilsBundle\Lib\Standalone\Urlizer;

/**
 * Comment
 * http://httpd.pl/onetomany?data=%28!o!-%28!cls!-!Namespace\\Point!*!field!-!odbiory!%29*!m!-%28!cls!-!Namespace\\Odbior!*!field!-!point!*!id!-!id!*!join!-!pointId!%29%29
 */
class Comment extends AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var DateTime
     */
    private $createdAt;

    /**
     * @var User
     */
    protected $user;

    protected $path;


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
     * Set comment
     *
     * @param string $comment
     * @return Comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set createdAt
     *
     * @param DateTime $createdAt
     * @return Comment
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return Punkt
     */
    public function getUser() {
        return $this->user;
    }

    public function setUser(User $user = null) {
        $this->user = $user;
        return $this;
    }
    function getPath() {
        return $this->path;
    }

    function setPath($path) {
        $this->path = $path;
        return $this;
    }


    public function getWebPath() {
        return '/bundles/stopsopautils/utils/lorem.jpg';
    }


}
