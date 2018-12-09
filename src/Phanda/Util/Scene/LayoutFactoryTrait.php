<?php

namespace Phanda\Util\Scene;

use Phanda\Contracts\Scene\Scene;
use Phanda\Exceptions\Scene\InvalidStageException;

trait LayoutFactoryTrait
{
    /**
     * @var array
     */
    protected $stages = [];

    /**
     * @var array
     */
    protected $stagesStack = [];

    /**
     * @var array
     */
    protected static $parentPlaceholder = [];

    /**
     * @var string $stage
     * @var string|null $content
     * @return $this
     */
    public function startStage($stage, $content = null)
    {
        if ($content === null) {
            if (ob_start()) {
                $this->stagesStack[] = $stage;
            }
        } else {
            $this->extendStage($stage, $content instanceof Scene ? $content : e($content));
        }

        return $this;
    }

    /**
     * @param string $stage
     * @param string $content
     * @return $this
     */
    protected function extendStage($stage, $content)
    {
        if (isset($this->stages[$stage])) {
            $content = str_replace(static::parentPlaceholder($stage), $content, $this->stages[$stage]);
        }

        $this->stages[$stage] = $content;
        return $this;
    }

    /**
     * Get the parent placeholder for the current request.
     *
     * @param  string $stage
     * @return string
     */
    public static function parentPlaceholder($stage = '')
    {
        if (!isset(static::$parentPlaceholder[$stage])) {
            static::$parentPlaceholder[$stage] = '##parent-placeholder-' . sha1($stage) . '##';
        }

        return static::$parentPlaceholder[$stage];
    }

    /**
     * @param bool $overwrite
     * @return string
     *
     * @throws InvalidStageException
     */
    public function stopStage($overwrite = false)
    {
        if (empty($this->stagesStack)) {
            throw new InvalidStageException("Cannot end a stage, when one has not been started.");
        }

        $lastStage = array_pop($this->stagesStack);

        if ($overwrite) {
            $this->stages[$lastStage] = ob_get_clean();
        } else {
            $this->extendStage($lastStage, ob_get_clean());
        }

        return $lastStage;
    }


    /**
     * Appends the current stage to a stage
     *
     * @return string
     *
     * @throws InvalidStageException
     */
    public function appendStage()
    {
        if (empty($this->stagesStack)) {
            throw new InvalidStageException("Cannot end a stage, when one has not been started.");
        }

        $lastStage = array_pop($this->stagesStack);

        if (isset($this->stages[$lastStage])) {
            $this->stages[$lastStage] .= ob_get_clean();
        } else {
            $this->stages[$lastStage] = ob_get_clean();
        }

        return $lastStage;
    }

    /**
     * Inserts content at the given stage
     *
     * @param $stage
     * @param string $default
     * @return mixed
     */
    public function insertContent($stage, $default = '')
    {
        $sectionContent = $default instanceof Scene ? $default : e($default);

        if (isset($this->stages[$stage])) {
            $sectionContent = $this->stages[$stage];
        }

        $sectionContent = str_replace('@@parent', '--parent--holder--', $sectionContent);

        return str_replace(
            '--parent--holder--', '@parent', str_replace(static::parentPlaceholder($stage), '', $sectionContent)
        );
    }

    /**
     * @return string
     */
    public function insertCurrentStage()
    {
        if (empty($this->stagesStack)) {
            return '';
        }

        return $this->insertContent($this->stopStage());
    }

    /**
     * Chekcs if a given stage exists
     *
     * @param $stage
     * @return bool
     */
    public function hasStage($stage)
    {
        return array_key_exists($stage, $this->stages);
    }

    /**
     * @param $stage
     * @param string|null $default
     * @return mixed
     */
    public function getStage($stage, $default = null)
    {
        return $this->stages[$stage] ?? $default;
    }

    /**
     * @return array
     */
    public function getAllStages()
    {
        return $this->stages;
    }

    /**
     * Clears the internal stages.
     * @return $this
     */
    public function clearStages()
    {
        $this->stages = [];
        $this->stagesStack = [];
        return $this;
    }
}