<?php

namespace Phanda\Util\Scene\Compiler\Bamboo;

trait CompilePhandaStatements
{
    /**
     * Compile the create statements into valid PHP.
     *
     * @param  string  $expression
     * @return string
     */
    protected function compileCreate($expression)
    {
        $segments = explode(',', preg_replace("/[\(\)\\\"\']/", '', $expression));
        $variable = trim($segments[0]);
        $service = trim($segments[1]);

        return "<?php \${$variable} = phanda()->create('{$service}'); ?>";
    }
}