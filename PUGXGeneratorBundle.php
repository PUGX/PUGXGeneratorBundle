<?php

namespace PUGX\GeneratorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PUGXGeneratorBundle extends Bundle
{
    public function getParent()
    {
        return 'SensioGeneratorBundle';
    }
}
