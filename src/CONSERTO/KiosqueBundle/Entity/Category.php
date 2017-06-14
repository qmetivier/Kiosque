<?php
// src/CONSERTO/KiosqueBundle/Entity/Category.php

namespace CONSERTO\KiosqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CONSERTO\KiosqueBundle\Repository\CategoryRepository")
 */
class Category
{
  /**
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ORM\Column(name="name", type="string", length=255)
   */
  private $name;

  /**
  * @ORM\ManyToMany(targetEntity="CONSERTO\KiosqueBundle\Entity\magazine", mappedBy="categories")
  */
  private $magazines;

  public function getId()
  {
    return $this->id;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function getName()
  {
    return $this->name;
  }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->magazines = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add magazine
     *
     * @param \CONSERTO\KiosqueBundle\Entity\magazine $magazine
     *
     * @return Category
     */
    public function addMagazine(\CONSERTO\KiosqueBundle\Entity\magazine $magazine)
    {
        $this->magazines[] = $magazine;

        $magazine->setCategory($this);

        return $this;
    }

    /**
     * Remove magazine
     *
     * @param \CONSERTO\KiosqueBundle\Entity\magazine $magazine
     */
    public function removeMagazine(\CONSERTO\KiosqueBundle\Entity\magazine $magazine)
    {
        $this->magazines->removeElement($magazine);
    }

    /**
     * Get magazines
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMagazines()
    {
        return $this->magazines;
    }
}
