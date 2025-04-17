<?php

namespace App;

use DI\ContainerBuilder;
class Container
{
    public static function build()
    {
        $containerBuilder = new ContainerBuilder();
        
        return $containerBuilder->build();
    }
} 