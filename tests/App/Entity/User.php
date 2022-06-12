<?php
namespace SYJS\JsBundle\Tests\App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 4,
     *      max = 30,
     * )
     * @ORM\Column(name="username", type="string", length=30)
     */
    private $username;
    /**
     * @var string
     * @Assert\Regex(
     *       pattern="/[0-9]+/",
     *       match=  true,
     * )
     * @Assert\Regex(
     *       pattern="/[a-zA-Z]+/",
     *       match=  true,
     * )
     * @ORM\Column(name="password", type="string", length=64)
     */
    private $password;
    
      /**
     * @var string
     * @Assert\Email(
     *   
     * )
     * @ORM\Column(name="email", type="string", length=30)
     */
    private $email;
}