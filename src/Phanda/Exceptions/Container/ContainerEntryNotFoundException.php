<?php

namespace Phanda\Exceptions\Container;

use Phanda\Exceptions\FatalPhandaException;
use Psr\Container\NotFoundExceptionInterface;

class ContainerEntryNotFoundException extends FatalPhandaException implements NotFoundExceptionInterface
{

}