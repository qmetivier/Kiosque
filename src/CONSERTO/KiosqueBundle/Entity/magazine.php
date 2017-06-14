<?php

namespace CONSERTO\KiosqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * magazine
 *
 * @ORM\Table(name="magazine")
 * @ORM\Entity(repositoryClass="CONSERTO\KiosqueBundle\Repository\magazineRepository")
 */
class magazine
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;


  	/**
  	 * @ORM\OneToOne(targetEntity="CONSERTO\KiosqueBundle\Entity\Pdf", cascade={"persist", "remove"})
   	*/
  	private $Pdf;

    /**
    * @ORM\ManyToMany(targetEntity="CONSERTO\KiosqueBundle\Entity\Category", cascade={"persist"}, inversedBy="magazines")
    * @ORM\JoinTable(name="CONSERTO_magazine_category")
    */
    private $categories;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return magazine
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return magazine
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return magazine
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    public function __construct()
    {
      // Par défaut, la date de l'annonce est la date d'aujourd'hui
      $this->date = new \Datetime();
    }


    /**
     * Set pdf
     *
     * @param \CONSERTO\KiosqueBundle\Entity\Pdf $pdf
     *
     * @return magazine
     */
    public function setPdf(\CONSERTO\KiosqueBundle\Entity\Pdf $pdf = null)
    {
        $this->Pdf = $pdf;

        return $this;
    }

    /**
     * Get pdf
     *
     * @return \CONSERTO\KiosqueBundle\Entity\Pdf
     */
    public function getPdf()
    {
        return $this->Pdf;
    }

    /**
     * Add category
     *
     * @param \CONSERTO\KiosqueBundle\Entity\Category $category
     *
     * @return magazine
     */
    public function addCategory(\CONSERTO\KiosqueBundle\Entity\Category $category)
    {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param \CONSERTO\KiosqueBundle\Entity\Category $category
     */
    public function removeCategory(\CONSERTO\KiosqueBundle\Entity\Category $category)
    {
        $this->categories->removeElement($category);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }
}
