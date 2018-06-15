<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class Index extends base
{
    public function index()
    {
        return $this->fetch();
    }

    public function index_mo()
    {
        return $this->fetch();
    }
}
