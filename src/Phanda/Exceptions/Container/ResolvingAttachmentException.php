<?php

namespace Phanda\Exceptions\Container;

use Phanda\Exceptions\FatalPhandaException;
use Psr\Container\ContainerExceptionInterface;

class ResolvingAttachmentException extends FatalPhandaException implements ContainerExceptionInterface
{

}