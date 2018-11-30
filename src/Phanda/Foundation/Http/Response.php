<?php


namespace Phanda\Foundation\Http;

use ArrayObject;
use JsonSerializable;
use Phanda\Contracts\Support\Arrayable;
use Phanda\Contracts\Support\Jsonable;
use Phanda\Util\Foundation\Http\ResponseTrait;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse
{
    use ResponseTrait;

    /**
     * @param mixed $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->original = $content;

        if ($this->shouldContentBeJSON($content)) {
            $this->addHeader('Content-Type', 'application/json');
            $content = $this->convertContentToJSON($content);
        } elseif ($content instanceof Renderable) {
            $content = $content->render();
        }

        parent::setContent($content);

        return $this;
    }

    /**
     * @param mixed $content
     * @return bool
     */
    protected function shouldContentBeJSON($content)
    {
        return $content instanceof Arrayable ||
            $content instanceof Jsonable ||
            $content instanceof ArrayObject ||
            $content instanceof JsonSerializable ||
            is_array($content);
    }

    /**
     * @param mixed $content
     * @return string
     */
    protected function convertContentToJSON($content)
    {
        if ($content instanceof Jsonable) {
            return $content->toJson();
        } elseif ($content instanceof Arrayable) {
            return json_encode($content->toArray());
        }

        return json_encode($content);
    }

}