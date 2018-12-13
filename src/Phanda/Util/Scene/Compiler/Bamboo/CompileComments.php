<?php

namespace Phanda\Util\Scene\Compiler\Bamboo;

trait CompileComments
{
    /**
     * Compile Bamboo comments into an empty string.
     *
     * @param  string  $value
     * @return string
     */
    protected function compileComments($value)
    {
        $pattern = sprintf('/%s\*(.*?)\*%s/s', $this->commentTags[0], $this->commentTags[1]);
        return preg_replace($pattern, '', $value);
    }
}