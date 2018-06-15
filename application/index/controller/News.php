<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class News extends base
{
    public function index()
    {
        return $this->fetch();
    }

    public function trade()
    {
        return $this->fetch();
    }

    public function lore()
    {
        return $this->fetch();
    }

}
