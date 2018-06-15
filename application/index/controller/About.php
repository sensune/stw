<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class About extends base
{
    public function index()
    {
        return $this->fetch();
    }


}
