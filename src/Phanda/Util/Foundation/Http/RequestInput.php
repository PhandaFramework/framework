<?php


namespace Phanda\Util\Foundation\Http;

use Phanda\Foundation\Http\Request;
use Phanda\Support\PhandArr;

/**
 * Trait RequestInput
 * @package Phanda\Util\Foundation\Http
 * @mixin Request
 */
trait RequestInput
{
    /**
     * @param string $source
     * @param string $key
     * @param string|array|null $default
     * @return string|array|null
     */
    protected function retrieveItem($source, $key, $default)
    {
        if (is_null($key)) {
            return $this->$source->all();
        }

        return $this->$source->get($key, $default);
    }

    /**
     * @param string $key
     * @param string|array|null $default
     * @return string|array|null
     */
    public function header($key = null, $default = null)
    {
        return $this->retrieveItem('headers', $key, $default);
    }

	/**
	 * Retrieve an input item from the request.
	 *
	 * @param  string|null  $key
	 * @param  string|array|null  $default
	 * @return string|array|null
	 */
	public function input($key = null, $default = null)
	{
		return data_get(
			$this->getInputSource()->all() + $this->query->all(), $key, $default
		);
	}

	/**
	 * Get an array of all of the files on the request.
	 *
	 * @return array
	 */
	public function allFiles()
	{
		return $this->files->all();
	}



	/**
	 * Determine if the request contains a given input item key.
	 *
	 * @param  string|array  $key
	 * @return bool
	 */
	public function has($key)
	{
		$keys = is_array($key) ? $key : func_get_args();

		$input = $this->all();

		foreach ($keys as $value) {
			if (! PhandArr::has($input, $value)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get all of the input and files for the request.
	 *
	 * @param  array|mixed  $keys
	 * @return array
	 */
	public function all($keys = null)
	{
		$input = array_replace_recursive($this->input(), $this->allFiles());

		if (! $keys) {
			return $input;
		}

		$results = [];

		foreach (is_array($keys) ? $keys : func_get_args() as $key) {
			PhandArr::set($results, $key, PhandArr::get($input, $key));
		}

		return $results;
	}

}