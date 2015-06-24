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







    protected function getUploadDir($tmp = null) {
        return str_replace('*', ($this->tempdir || $tmp) ? '_temp' : '', '/media/uploads/user*');
    }
    protected function getUploadRootDir($tmp = null) {
        return __DIR__ . '/../../../../../../../web' . $this->getUploadDir($tmp);
    }

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
    /**
     * @Assert\File(maxSize="6000000")
     */
    protected $file;
    public $tempdir;
    /**
     * http://symfony.com/doc/current/cookbook/doctrine/file_uploads.html
     */

    public function getAbsolutePath($path = null, $tmp = null)
    {
        if ($path) {
            return null === $path ? null : $this->getUploadRootDir($tmp).'/'.$path;
        }

        return null === $this->path ? null : $this->getUploadRootDir($tmp).'/'.$this->path;
    }

    public function getWebPath()
    {
        if ($this->path) {
            $file = $this->getAbsolutePath();

            if (file_exists($file)) {
                return $this->getUploadDir().'/'.$this->path;
            }
            else {
                return $this->getUploadDir(true).'/'.$this->path;
            }
        }
        return null;
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
//        niechginie('a co tutaj robimy?');
        $this->file = $file;
        // check if we have an old image path
        if (is_file($this->getAbsolutePath())) {
            // store the old name to delete after the update
            $this->temp = $this->getAbsolutePath();
        } else {
            // tutaj można wykonać przypisanie czegoś domyślnego
//            $this->path = 'initial';
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

    public function preUpload($moveFromTmp = false) {
        if (gettype($moveFromTmp) === 'boolean' && $moveFromTmp && $this->path) {
            $tmp = $this->getAbsolutePath(null, true);
            if (file_exists($tmp)) {
                $target = $this->getAbsolutePath();
                UtilFilesystem::rename($tmp, $this->getAbsolutePath());
                UtilFilesystem::removeEmptyDirsToPath(
                    pathinfo($tmp, PATHINFO_DIRNAME),
                    $this->getUploadRootDir(true)
                );
            }
        }
        if (null !== $this->getFile()) {
//            niechginie('tutaj też nie powinno nas być');
            // do whatever you want to generate a unique name

            $file = $this->getFile();

            $this->path = Urlizer::urlizeCaseSensitiveTrim(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));

            $ext = $file->guessExtension()
                    ;

            if (!$ext) {
                $ext = 'bin';
            }

            $this->path .= '.' . $ext;

            do {
                $dir = substr(md5(uniqid(mt_rand(), true)), 0, 5);

                $dir[2] = '/';

                $absolutedir = $this->getAbsolutePath($dir);

                $tmpabsolutedir = $this->getAbsolutePath($dir, true);

            } while (file_exists($absolutedir . '/' . $this->path) || file_exists($tmpabsolutedir . '/' . $this->path));

            $this->path = $dir . '/' . $this->path;
        }
    }
    public function upload() {
        if (null === $this->getFile()) {
//            $tmp = $this->getAbsolutePath(null, true);
//            if (file_exists($tmp)) {
//                UtilFilesystem::rename($tmp, $this->getAbsolutePath());
//            }
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
    /**
     * Livecycle callback
     */
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
