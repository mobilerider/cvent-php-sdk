<?php

namespace Mr\Cvent\Interfaces;


interface ArrayEncoder
{
    /**
     * @param array $data
     * @param bool $pretty
     * @return string
     */
    public function encode(array $data, $pretty = false);

    /**
     * @param $stream
     * @return array
     */
    public function decode($stream);
}
