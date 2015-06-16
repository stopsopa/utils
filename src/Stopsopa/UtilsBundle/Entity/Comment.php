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

    /**
     * @var string
     */
    public $path;

    /**
     * reutrn DateTime
     */
    protected $updatedAt;

    public function __construct() {
        $this->updatedAt = new DateTime();
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
    function getUpdatedAt() {
        return $this->updatedAt;
    }

    function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;
    }








    /**
     * http://symfony.com/doc/current/cookbook/doctrine/file_uploads.html
     */

    public function getAbsolutePath($path = null)
    {
        if ($path) {
            return null === $path ? null : $this->getUploadRootDir().'/'.$path;
        }

        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir($temp = false)
    {
        // the absolute directory path where uploaded
        // documents should be saved
        $dir = __DIR__.'/../../../../../../../web'. ($temp ? $this->getUploadTmpDir() : $this->getUploadDir());

        return $dir;
    }

    protected function getUploadDir()
    {
        return '/media/uploads/comments';
    }
    protected function getUploadTmpDir() {
        return '/media/uploads/comments_tmp';
    }




    /**
     * @Assert\File(maxSize="6000000")
     */
    private $file;

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        // check if we have an old image path
        if (is_file($this->getAbsolutePath())) {
            // store the old name to delete after the update
            $this->temp = $this->getAbsolutePath();
        } else {
            $this->path = 'initial';
        }
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    public function preUpload()
    {
        if (null !== $this->getFile()) {
            // do whatever you want to generate a unique name

            $file = $this->getFile();

            $this->path = Urlizer::urlizeCaseSensitiveTrim(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));

            $ext = $file->guessExtension();

            if (!$ext) {
                $ext = 'bin';
            }

            $this->path .= '.' . $ext;

            do {
                $dir = substr(md5(uniqid(mt_rand(), true)), 0, 5);

                $dir[2] = '/';

                $absolutedir = $this->getAbsolutePath($dir);

            } while (file_exists($absolutedir . '/' . $this->path));

            $this->path = $dir . '/' . $this->path;
        }
    }
    public function upload() {

        header('X-test'.uniqid().': 1');

        if (null === $this->getFile()) {
            return;
        }

        // check if we have an old image
        if (!empty($this->temp)) {
            $this->_removeFile($this->temp);
        }

        // you must throw an exception here if the file cannot be moved
        // so that the entity is not persisted to the database
        // which the UploadedFile move() method does
        $this->getFile()->move(
            $this->getUploadRootDir() . '/' . pathinfo($this->path, PATHINFO_DIRNAME),
            pathinfo($this->path, PATHINFO_BASENAME)
        );

        $this->setFile(null);
    }
    public function storeFilenameForRemove()
    {
        $this->temp = $this->getAbsolutePath();
    }

    public function removeUpload()
    {
        if (!empty($this->temp)) {
            $this->_removeFile($this->temp);
        }
    }
    protected function _removeFile($file) {
        if (isset($file) && file_exists($file)) {
            unlink($file);
        }

        UtilFilesystem::removeEmptyDirsToPath(
            pathinfo($file, PATHINFO_DIRNAME),
            $this->getUploadRootDir()
        );
    }
}
