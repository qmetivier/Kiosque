<?php

namespace CONSERTO\KiosqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Pdf
 *
 * @ORM\Table(name="pdf")
 * @ORM\Entity(repositoryClass="CONSERTO\KiosqueBundle\Repository\PdfRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Pdf
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
     *
     * @ORM\Column(name="url_img", type="string", length=255)
     */
    private $urlImg;

    /**
     * @var string
     *
     * @ORM\Column(name="alt_img", type="string", length=255)
     */
    private $altImg;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255)
     */
    private $link;


    private $Img;
    private $tempImgName;

    private $Pdf;
    private $tempPdfName;


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
     * Set urlImg
     *
     * @param string $urlImg
     *
     * @return Pdf
     */
    public function setUrlImg($urlImg)
    {
        $this->urlImg = $urlImg;

        return $this;
    }

    /**
     * Get urlImg
     *
     * @return string
     */
    public function getUrlImg()
    {
        return $this->urlImg;
    }

    /**
     * Set altImg
     *
     * @param string $altImg
     *
     * @return Pdf
     */
    public function setAltImg($altImg)
    {
        $this->altImg = $altImg;

        return $this;
    }

    /**
     * Get altImg
     *
     * @return string
     */
    public function getAltImg()
    {
        return $this->altImg;
    }

    /**
     * Set link
     *
     * @param string $link
     *
     * @return Pdf
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    public function getImg()
    {
      return $this->Img;
    }

    public function setImg(UploadedFile $Img)
    {
      $this->Img = $Img;
      if(null !== $this->urlImg){
        $this->tempImgName = $this->urlImg;

        $this->urlImg = null;
        $this->altImg = null;
      }
    }

    public function getPdf()
    {
      return $this->Pdf;
    }

    public function setPdf(UploadedFile $Pdf = null)
    {
      $this->Pdf = $Pdf;
    }


    /**
   * @ORM\PrePersist()
   * @ORM\PreUpdate()
   */
   public function preUpload()
   {
     // Si jamais il n'y a pas de fichier (champ facultatif), on ne fait rien
     if ( null === $this->Pdf) {
          return;
        }
        var_dump($this->Pdf);
        // Le nom du fichier est son id, on doit juste stocker également son extension
        // Pour faire propre, on devrait renommer cet attribut en « extension », plutôt que « url »
      $this->urlImg = 'jpg';

      // Et on génère l'attribut alt de la balise <img>, à la valeur du nom du fichier sur le PC de l'internaute
      $this->altImg = $this->Pdf->getClientOriginalName();

      $this->link = $this->Pdf->guessExtension();
    }

    /**
    * @ORM\PostPersist()
    * @ORM\PostUpdate()
    */
    public function upload()
    {
      // Si jamais il n'y a pas de fichier (champ facultatif), on ne fait rien
      if ( null === $this->Pdf) {
        return;
      }

      // Si on avait un ancien fichier, on le supprime
      if (null !== $this->tempPdfName) {
        $oldFile = $this->getUploadRootDir().'/'.$this->id.'.'.$this->tempPdfName;
        if (file_exists($oldFile)) {
          unlink($oldFile);
        }
      }

      // On déplace le fichier envoyé dans le répertoire de notre choix
        exec('C:/ImageMagick/magick convert '.$this->Pdf->getpathName().'[0] C:/wamp64/www/Symfony/web/uploads/img/'.$this->id.'.jpg');
      // On déplace le fichier envoyé dans le répertoire de notre choix
      $this->Pdf->move(
        $this->getUploadRootDirPDF(), // Le répertoire de destination
        $this->id.'.'.$this->link   // Le nom du fichier à créer, ici « id.extension »
      );
  }


    /**
    * @ORM\PreRemove()
    */
    public function preRemoveUpload()
    {
      // On sauvegarde temporairement le nom du fichier, car il dépend de l'id
      $this->tempImgName = $this->getUploadRootDirImg().'/'.$this->id.'.'.$this->urlImg;
      $this->tempPdfName = $this->getUploadRootDirPDF().'/'.$this->id.'.'.$this->link;
    }

    /**
    * @ORM\PostRemove()
    */
    public function removeUpload()
    {
      // En PostRemove, on n'a pas accès à l'id, on utilise notre nom sauvegardé
      if (file_exists($this->tempImgName)) {
        // On supprime le fichier
        unlink($this->tempImgName);
      }
      if (file_exists($this->tempPdfName)) {
        // On supprime le fichier
        unlink($this->tempPdfName);
      }
    }

      public function getUploadDirImg()
      {
        // On retourne le chemin relatif vers l'image pour un navigateur (relatif au répertoire /web donc)
        return 'uploads/img';
      }

      protected function getUploadRootDirImg()
      {
        // On retourne le chemin relatif vers l'image pour notre code PHP
        return __DIR__.'/../../../../web/'.$this->getUploadDirImg();
      }

        public function getUploadDirPDF()
        {
          // On retourne le chemin relatif vers l'image pour un navigateur (relatif au répertoire /web donc)
          return 'uploads/pdf';
        }

        protected function getUploadRootDirPDF()
        {
          // On retourne le chemin relatif vers l'image pour notre code PHP
          return __DIR__.'/../../../../web/'.$this->getUploadDirPDF();
        }

        function clearFolder($folder)
        {
          // 1 ouvrir le dossier
          $dossier=opendir($folder);
          //2)Tant que le dossier est aps vide
          while ($fichier = readdir($dossier))
          {
            //3) Sans compter . et ..
            if ($fichier != "." && $File != "..")
            {
              //On selectionne le fichier et on le supprime
              $Vidage= $dossier.$fichier;
              unlink($Vidage);
            }
          }
          //Fermer le dossier vide
          closedir($dossier);
        }

}
