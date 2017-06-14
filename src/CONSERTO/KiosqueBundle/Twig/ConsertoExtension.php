<?php

namespace CONSERTO\KiosqueBundle\Twig;

class ConsertoExtension extends \Twig_Extension
{

  public function getFunctions(){
    return array(
      'randomColor' => new \Twig_Function_Method($this, 'random_color'),
    );
  }

  public function random_color() {
    $tab = '';
    for ($i=0; $i < 3 ; $i++) {
      $tab = $tab.str_pad( dechex( mt_rand( 100, 255 ) ), 2, '0', STR_PAD_LEFT);
    }
    return $tab;
  }

}
