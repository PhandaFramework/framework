<?php

namespace Phanda\Util\Scene\Compiler\Bamboo;

trait CompilePHPStatements
{
    /**
     * Compile the raw PHP statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compilePhp($expression)
    {
        if ($expression) {
            return "<?php {$expression}; ?>";
        }

        return '@php';
    }

    /**
     * Compile the unset statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileUnset($expression)
    {
        return "<?php unset{$expression}; ?>";
    }

    /**
     * Compile the unset statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileSet($expression)
    {
        $expression = $this->stripParentheses($expression);
        $segments = explode(',', $expression);
        $variable = trim(preg_replace("/[\(\)\\\"\']/", '', $segments[0]));
        $value = trim($segments[1]);

        return "<?php \${$variable} = {$value}; ?>";
    }
}