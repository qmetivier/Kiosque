<?php

namespace CONSERTO\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CONSERTOUserBundle extends Bundle
{
  public function getParent()
  {
    return 'FOSUserBundle';
  }
}
