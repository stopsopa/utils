<?php

namespace Stopsopa\UtilsBundle\Entity;

use DateTime;
use Stopsopa\UtilsBundle\Lib\FileProcessors\CommentFileProcessor;

/**
 * Comment
 * http://httpd.pl/onetomany?data=%28!o!-%28!cls!-!Namespace\\Point!*!field!-!odbiory!%29*!m!-%28!cls!-!Namespace\\Odbior!*!field!-!point!*!id!-!id!*!join!-!pointId!%29%29.
 */
class Comment extends AbstractEntity
{
    /**
     * @var int
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

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set comment.
     *
     * @param string $comment
     *
     * @return Comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set createdAt.
     *
     * @param DateTime $createdAt
     *
     * @return Comment
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
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
    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    public function getWebPath()
    {
        if ($this->path) {
            $config = CommentFileProcessor::getConfig();

            $tmp = $config['web'].$config['dirtmp'].$this->path;
            if (file_exists($tmp)) {
                return $config['dirtmp'].$this->path;
            }

            return $config['dir'].$this->path;
        }

        return '/bundles/stopsopautils/utils/lorem.jpg';
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public $file;
    protected $path;
    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }
}
