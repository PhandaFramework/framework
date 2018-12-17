<?php

namespace Phanda\Util\Scene\Compiler\Bamboo;

trait CompilePresenterStatements
{
	/**
	 * Compile the @presenter statements into valid PHP.
	 *
	 * @param  string $expression
	 * @return string
	 */
	protected function compilePresenter($expression)
	{
		$segments = explode(',', preg_replace("/[\(\)\\\"\']/", '', $expression));
		$variable = trim($segments[0]);
		$presenter = trim(explode('::class', $segments[1])[0]);

		return "<?php \${$variable} = new {$presenter}(\$this); ?>";
	}

	/**
	 * Compile the @present statements into valid PHP.
	 *
	 * @param  string $expression
	 * @return string
	 */
	protected function compilePresent($expression)
	{
		$segments = explode(',', preg_replace("/[\(\)\\\"\']/", '', $expression));
		$presenter = trim($segments[0]);
		$method = trim($segments[1]);

		return "<?php echo \${$presenter}->{$method}(); ?>";
	}
}