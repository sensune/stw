<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class News extends base
{
    public function index()
    {
        $list = Db::name('news')->where('classify','公司新闻')->paginate(9);
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function trade()
    {
        $list = Db::name('news')->where('classify','行业新闻')->paginate(9);
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function lore()
    {
        $list = Db::name('news')->where('classify','电子知识')->paginate(9);
        $this->assign('list', $list);
        return $this->fetch();
    }

}
