<?php

namespace Phanda\Scene\Bamboo;

use Phanda\Scene\Compiler\AbstractCompiler;

class Compiler extends AbstractCompiler
{

    /**
     * @var string The file being compiled
     */
    protected $path;

    /**
     * @var array
     */
    protected $extensions = [];

    /**
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
    protected $rawTags = ['{%', '%}'];

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
    protected $escapedTags = ['{|', '|}'];

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
        $this->header = [];

        if (strpos($value, '@php')) {
            $value = $this->storePhpBlocks($value);
        }

        $result = '';

        if (strlen($this->header) > 0) {
            $result .= $this->header;
        }

        foreach(token_get_all($value) as $token) {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }

        if(!empty($this->rawPhpBlocks)) {
            $result = $this->restoreRawContent($result);
        }

        if(count($this->footer) > 0) {
            $result = $this->addFooters($this->footer);
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
     * @param array $token
     * @return string
     */
    protected function parseToken(array $token)
    {

    }

    /**
     * @param array $footers
     * @return string
     */
    protected function addFooters(array $footers)
    {

    }
}