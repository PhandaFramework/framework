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

		return "<?php \${$variable} = new {$presenter}(\$__scene); ?>";
	}

	/**
	 * Compile the @present statements into valid PHP.
	 *
	 * @param  string $expression
	 * @return string
	 */
	protected function compilePresent($expression)
	{
	}
}