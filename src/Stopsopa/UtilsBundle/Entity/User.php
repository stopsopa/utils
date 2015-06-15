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

    /**
     * reutrn DateTime
     */
    protected $updatedAt;

    /**
     * @var string
     */
    public $path;

    public function __construct() {
        $this->comments = new ArrayCollection();
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

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        $dir = __DIR__.'/../../../../../../../web'.$this->getUploadDir();

        return $dir;
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return '/media/uploads/user';
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


            // tu jest dosyć ważny kawałek kodu
            // tu jest dosyć ważny kawałek kodu
            // tu jest dosyć ważny kawałek kodu
            foreach ($this->getComments() as $c) {
                $c->preUpload();
            }
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
