<?php

namespace Phanda\Util\Scene\Compiler\Bamboo;

trait CompileLayoutStatements
{

    /**
     * @var string
     */
    protected $lastStage;

    /**
     * Extends a scene, and appends it to the footer.
     *
     * @param $expression
     * @return string
     */
    protected function compileExtends($expression)
    {
        $expression = $this->stripParentheses($expression);

        $output = "<?php echo \$__scene->make({$expression}), \Phanda\Support\PhandArr::except(get_defined_vars(), ['data', 'path']))->render(); ?>";
        $this->footer[] = $output;

        return '';
    }

    /**
     * Extends a stage in a scene element.
     *
     * @param $expression
     * @return string
     */
    protected function compileStage($expression)
    {
        return '';
    }

    protected function compileEndstage($expression)
    {
        return '';
    }

}