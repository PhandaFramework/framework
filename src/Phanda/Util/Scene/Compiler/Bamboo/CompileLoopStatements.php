<?php

namespace Phanda\Util\Scene\Compiler\Bamboo;

trait CompileLoopStatements
{
    /**
     * Compile the for statements into valid PHP.
     *
     * @param  string $expression
     * @return string
     */
    protected function compileFor($expression)
    {
        return "<?php for{$expression}: ?>";
    }

    /**
     * Compile the for-each statements into valid PHP.
     *
     * @param  string $expression
     * @return string
     */
    protected function compileForeach($expression)
    {
        preg_match('/\( *(.*) +as *(.*)\)$/is', $expression, $matches);
        $iterator = trim($matches[1]);
        $iteration = trim($matches[2]);

        return "<?php foreach({$iterator} as {$iteration}): ?>";
    }

    /**
     * Compile the break statements into valid PHP.
     *
     * @param  string $expression
     * @return string
     */
    protected function compileBreak($expression)
    {
        if ($expression) {
            preg_match('/\(\s*(-?\d+)\s*\)$/', $expression, $matches);

            return $matches ? '<?php break ' . max(1, $matches[1]) . '; ?>' : "<?php if{$expression} break; ?>";
        }

        return '<?php break; ?>';
    }

    /**
     * Compile the continue statements into valid PHP.
     *
     * @param  string $expression
     * @return string
     */
    protected function compileContinue($expression)
    {
        if ($expression) {
            preg_match('/\(\s*(-?\d+)\s*\)$/', $expression, $matches);

            return $matches ? '<?php continue ' . max(1, $matches[1]) . '; ?>' : "<?php if{$expression} continue; ?>";
        }

        return '<?php continue; ?>';
    }

    /**
     * Compile the end-for statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndfor()
    {
        return '<?php endfor; ?>';
    }

    /**
     * Compile the end-for-each statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndforeach()
    {
        return '<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>';
    }

    /**
     * Compile the while statements into valid PHP.
     *
     * @param  string $expression
     * @return string
     */
    protected function compileWhile($expression)
    {
        return "<?php while{$expression}: ?>";
    }

    /**
     * Compile the end-while statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndwhile()
    {
        return '<?php endwhile; ?>';
    }
}