<?php

namespace CONSERTO\KiosqueBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * magazineRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class magazineRepository extends \Doctrine\ORM\EntityRepository
{

  public function getmagazineWithCategoriesAndSearch($string, $page, $nbelementparpage, $sorts)
  {
    $search= '';
    $tab = explode(" ",$string);
    foreach ($tab as $key) {
      $key = "%".$key."%";
      $search = $search.$key." OR LIKE ";
      $search = substr($search, 0, -9);
    }
    if ($sorts == 'date'){
          $paramSearch = "desc";
    }else{
          $paramSearch = "asc";
    }

    $qb = $this->createQueryBuilder('m');

    $qb
      ->where('m.title LIKE :string OR m.content LIKE :string')
      ->setParameter('string', $search);
    $qb->orderBy("m.$sorts",$paramSearch);

    // Enfin, on retourne le résultat
     return $qb
      ->getQuery()
      ->setMaxResults($nbelementparpage)
      ->setFirstResult(0+$page*$nbelementparpage)
      ->getResult()
    ;
  }

  public function getNbDocInListeWithCategoriesAndSearch($string)
  {
    $search= '';
    $tab = explode(" ",$string);
    foreach ($tab as $key) {
      $key = "%".$key."%";
      $search = $search.$key." OR LIKE ";
      $search = substr($search, 0, -9);
    }
    $qb = $this->createQueryBuilder('m');

    return $qb
      ->select('COUNT(m)')
      ->where('m.title LIKE :string OR m.content LIKE :string')
      ->setParameter('string', $search)
      ->getQuery()
      ->getSingleScalarResult();
  }

  public function getNbDocInListe()
  {
    return $this->createQueryBuilder('m')
      ->select('COUNT(m)')
      ->getQuery()
      ->getSingleScalarResult();
  }

}
