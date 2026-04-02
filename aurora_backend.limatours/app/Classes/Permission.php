<?php

namespace App\Classes;


class Permission
{
    public $name;
    public $actions;

    public function __construct($name, $actions = array()) {
        $this->name = $name;
        $this->actions = $actions;
    }

}
