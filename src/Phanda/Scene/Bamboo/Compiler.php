<?php

namespace Phanda\Scene\Bamboo;

use Phanda\Contracts\Scene\Compiler\ExtendableCompiler;
use Phanda\Scene\Compiler\AbstractCompiler;
use Phanda\Support\PhandArr;
use Phanda\Support\PhandaStr;
use Phanda\Util\Scene\Compiler\Bamboo\CompileComments;
use Phanda\Util\Scene\Compiler\Bamboo\CompileConditionalStatements;
use Phanda\Util\Scene\Compiler\Bamboo\CompileDebugStatements;
use Phanda\Util\Scene\Compiler\Bamboo\CompileLoopStatements;
use Phanda\Util\Scene\Compiler\Bamboo\CompileOutputStatements;
use Phanda\Util\Scene\Compiler\Bamboo\CompilePhandaStatements;
use Phanda\Util\Scene\Compiler\Bamboo\CompilePHPStatements;

class Compiler extends AbstractCompiler implements ExtendableCompiler
{

    use CompileComments,
        CompileConditionalStatements,
        CompileDebugStatements,
        CompileLoopStatements,
        CompileOutputStatements,
        CompilePhandaStatements,
        CompilePHPStatements;

    /**
     * @var string The file being compiled
     */
    protected $path;

    /**
     * @var array
     */
    protected $extensions = [];

    /**
     * These are the internal functions that get called on the compiler. The function format is compile{$compiler}
     * For example the 'Extensions' will call compileExtensions() internally, and return the result.
     *
     * The order in which these appear is important, as it will strip out the Bamboo tag from the string and replace it
     * with the actual value. Hence why we put comments at the start so that they can be omitted and any functionality
     * contained in a comment is ignored.
     *
     * @var array
     */
    protected $compilers = [
        "Comments",
        "Extensions",
        "Statements",
        "Echos"
    ];

    /**
     * Opening and closing tags for raw echos
     *
     * @var array
     */
    protected $rawTags = ['{\%', '\%}'];

    /**
     * Opening and closing tags for outputting of variables and other miscs
     *
     * @var array
     */
    protected $outputTags = ['{{', '}}'];

    /**
     * Opening and closing tags for escaped outputs
     *
     * @var array
     */
    protected $escapedTags = ['{\|', '\|}'];

    /**
     * @var string
     */
    protected $echoFormat = 'e(%s)';

    /**
     * @var string
     */
    protected $header = "<?php /* Compiled by the Phanda Bamboo Compiler */ ?>";

    /**
     * @var array
     */
    protected $footer = [];

    /**
     * An array to store raw blocks of php to be readded after compilation
     * @var array
     */
    protected $rawPhpBlocks = [];

    /**
     * @var string $path
     * @return void
     */
    public function compileScene($path)
    {
        $this->setPath($path);

        $compiledBamboo = $this->compileString(
            $this->filesystem->loadFile($this->getPath())
        );

        $this->filesystem->saveContents(
            $this->getPathToCompiledScene($this->getPath()),
            $compiledBamboo
        );
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param string $header
     * @return $this
     */
    public function setHeader($header)
    {
        $this->header = $header;
        return $this;
    }

    /**
     * @param string $value
     * @return string
     */
    public function compileString($value)
    {
        $this->footer = [];

        if (strpos($value, '@php')) {
            $value = $this->storePhpBlocks($value);
        }

        $result = '';

        if (strlen($this->header) > 0) {
            $result .= $this->header . PHP_EOL;
        }

        foreach (token_get_all($value) as $token) {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }

        if (!empty($this->rawPhpBlocks)) {
            $result = $this->restoreRawContent($result);
        }

        if (count($this->footer) > 0) {
            $result = $this->addFooters($result);
        }

        return $result;
    }

    /**
     * Store the PHP blocks and replace them with a temporary placeholder.
     *
     * @param  string $value
     * @return string
     */
    protected function storePhpBlocks($value)
    {
        return preg_replace_callback('/(?<!@)@php(.*?)@endphp/s', function ($matches) {
            return $this->storeRawBlock("<?php{$matches[1]}?>");
        }, $value);
    }

    /**
     * Store a raw block and return a unique raw placeholder.
     *
     * @param  string $value
     * @return string
     */
    protected function storeRawBlock($value)
    {
        return $this->getRawPlaceholder(
            array_push($this->rawPhpBlocks, $value) - 1
        );
    }

    /**
     * Replace the raw placeholders with the original code stored in the raw blocks.
     *
     * @param  string $result
     * @return string
     */
    protected function restoreRawContent($result)
    {
        $result = preg_replace_callback('/' . $this->getRawPlaceholder('(\d+)') . '/', function ($matches) {
            return $this->rawPhpBlocks[$matches[1]];
        }, $result);

        $this->rawPhpBlocks = [];

        return $result;
    }

    /**
     * Get a placeholder to temporary mark the position of raw blocks.
     *
     * @param  int|string $replace
     * @return string
     */
    protected function getRawPlaceholder($replace)
    {
        return str_replace('#', $replace, '@__raw_block_#__@');
    }

    /**
     * @param string $result
     * @return string
     */
    protected function addFooters($result)
    {
        return ltrim($result, PHP_EOL)
            . PHP_EOL . implode(PHP_EOL, array_reverse($this->footer));
    }

    /**
     * @param array $token
     * @return string
     */
    protected function parseToken(array $token)
    {
        [$id, $content] = $token;

        if ($id == T_INLINE_HTML) {
            foreach ($this->compilers as $type) {
                $content = $this->{"compile{$type}"}($content);
            }
        }

        return $content;
    }

    /**
     * Execute the user defined extensions.
     *
     * @param  string $value
     * @return string
     */
    protected function compileExtensions($value)
    {
        foreach ($this->extensions as $compiler) {
            $value = call_user_func($compiler, $value, $this);
        }

        return $value;
    }

    /**
     * Compile Bamboo statements that start with "@".
     *
     * @param  string $value
     * @return string
     */
    protected function compileStatements($value)
    {
        return preg_replace_callback(
            '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x',
            function ($match) {
                return $this->compileStatement($match);
            }, $value
        );
    }

    /**
     * Compile a single Bamboo @ statement.
     *
     * @param  array $match
     * @return string
     */
    protected function compileStatement($match)
    {
        if (PhandaStr::contains('@', $match[1])) {
            $match[0] = isset($match[3]) ? $match[1] . $match[3] : $match[1];
        } elseif (method_exists($this, $method = 'compile' . ucfirst($match[1]))) {
            $match[0] = $this->$method(PhandArr::get($match, 3));
        }

        return isset($match[3]) ? $match[0] : $match[0] . $match[2];
    }

    /**
     * Strip the parentheses from the given expression.
     *
     * @param  string $expression
     * @return string
     */
    public function stripParentheses($expression)
    {
        if (PhandaStr::startsIn('(', $expression)) {
            $expression = substr($expression, 1, -1);
        }

        return $expression;
    }

    /**
     * Register a custom Bamboo compiler.
     *
     * @param  callable $extensionCompiler
     * @return $this
     */
    public function extend(callable $extensionCompiler)
    {
        $this->extensions[] = $extensionCompiler;
        return $this;
    }

    /**
     * Get the extensions used by the compiler.
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
}