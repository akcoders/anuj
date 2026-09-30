<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Char_counter
{
    public function count($content)
    {
        return mb_strlen((string) $content, 'UTF-8');
    }
}
