<?php

namespace Phanda\Presenter;

use Phanda\Scene\Engine\SceneCompilerEngine;
use Phanda\Scene\Factory;

abstract class AbstractPresenter
{
	/**
	 * @var SceneCompilerEngine
	 */
	protected $scene;

	/**
	 * @var \Phanda\Scene\Factory
	 */
	protected $sceneFactory;

	/**
	 * AbstractPresenter constructor.
	 *
	 * @param SceneCompilerEngine $scene
	 */
	public function __construct(SceneCompilerEngine $scene)
	{
		$this->scene = $scene;
		$this->sceneFactory = $this->scene->getScene()->getFactory();
		$this->initialize();
	}

	/**
	 * As Phanda configures and handles all the dependencies for providers, we provide this function which gets called
	 * immediately after the constructor has completed resolving. You can use this function to configure further any
	 * specific customization needs you might have for your presenter.
	 *
	 * We do this as to avoid accidental logic mistakes when updating/overriding the constructor.
	 *
	 * Being a presenter, this class has a property set on it called $scene which can be used to modify, and affect the
	 * scene as needed. You can simply access this by calling `$this->scene` at any time. Any public functions defined
	 * in this presenter can also be called by using `@present` in the bamboo file. These functions can either return
	 * content which will be injected into the scene, or alternatively can inject variables and other data into the
	 * scene, for the scene to be able to use.
	 *
	 * A presenter should be used strictly for handling view logic, and is a way to avoid including raw PHP in your
	 * scene files.
	 *
	 * It's import to note however, that in the current version of Phanda, any variables that you define by using
	 * $this->scene->share('variable', 'value'), can only be accessed in the scene by using `$this->variable`.
	 * Currently there is no global way of sharing a variable after the scene has started rendering.
	 *
	 * @return void
	 */
	abstract protected function initialize();

	protected final function share($key, $value)
	{
		$this->scene->getScene()->attach($key, $value);
	}

}