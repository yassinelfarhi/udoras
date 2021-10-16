<?php

namespace FS\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FSUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
