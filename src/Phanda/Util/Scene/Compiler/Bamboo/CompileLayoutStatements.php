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
        $this->lastStage = trim($expression, "()'\" ");
        return "<?php echo \$__scene->startStage{$expression}; ?>";
    }

    /**
     * Stops a stage extension
     *
     * @return string
     */
    protected function compileEndstage()
    {
        return "<?php echo \$__scene->stopStage(); ?>";
    }

    /**
     * Renders a stage in a layout
     *
     * @param $expression
     * @return string
     */
    protected function compileRenderstage($expression)
    {
        return "<?php echo \$__scene->insertContent{$expression}; ?>";
    }

    /**
     * Renders the current stage in a layout
     *
     * @return string
     */
    protected function compileShow()
    {
        return "<?php echo \$__scene->insertCurrentStage(); ?>";
    }

}