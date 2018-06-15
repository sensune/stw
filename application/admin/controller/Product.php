<?php
/**
 * @Author: Sensune  <sensume@163.com> 
 * @Date: 2018-06-08 10:46:40 
 * @Last Modified by: Sensune
 * @Last Modified time: 2018-06-15 17:11:24
 */

namespace app\admin\controller;
use app\admin\controller\Base;
use think\Db;
use think\Request;

class Product extends Base
{
    /**
     * 产品列表
     */
    public function index()
    {
        $data = Db::table('product')->select();
        $this->assign('data',$data);
        return $this->fetch();
    }

    /**
     * 产品添加
     */
    public function product_add()
    {
        if ($this->request->method() == "POST") {
            // 获取表单上传文件 例如上传了001.jpg
            $file = request()->file('excel');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate([
                'size'=>5242880,
                'ext'=>'xls,xlsx,csv'
            ])->move(ROOT_PATH . 'public' . DS . 'uploads');
             
            if ($info) {
                $file_path = $info->getSaveName();
                $file_name = $info->getFileName();
     
               
                
                // 引入PHPEXCEL类库
                import('PHPExcel_IOFactory',  "/vendor/phpoffice/PhpExcel/PHPExcel/");
                import('PHPExcel', "/vendor/phpoffice/PhpExcel/");
                // 判断文件版本，选择对应的解析文件
                if ('xlsx' == $info->getExtension()) {
                    import('PHPExcel_Reader_Excel2007',  "/vendor/phpoffice/PhpExcel/PHPExcel/Reader/");
                    $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
                } else {
                    import('PHPExcel_Reader_Excel5',  "/vendor/phpoffice/PhpExcel/PHPExcel/Reader/");
                    $objReader = \PHPExcel_IOFactory::createReader('Excel5');
                }
                $full_path = ROOT_PATH . "public/uploads/" . $file_path;
                 
                // 解析Excel文件
                $objPHPExcel = $objReader->load($full_path);

                // 读取第一个工作表（编号从0开始）
                $sheet = $objPHPExcel->getSheet(0);
                // 取得总行数
                $highestRow = $sheet->getHighestRow();
                // 取得总列数
                $highestColumn = $sheet->getHighestColumn();
                // 循环读取excel文件,读取一条,插入数组一条
                for ($j=4;$j<=$highestRow;$j++) {
                    for ($k='A';$k<=$highestColumn;$k++) {
                        // 读取单元格
                        $examPaper_arr[$j][$k] = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                    }
                }
                 
                // 从Excel提取images
                $image_info = process_excel_image($file_name, $full_path);
                var_dump($image_info);die;
                // 导入成功总数
                $sum = 0;
                // 重复总数
                $user_repeat = 0;
                $error_num = 0;
                // 开启事务
                // Db::startTrans();
                // try {
                foreach ($examPaper_arr as $key=>$value) { // 教师记录信息
                    if ($this->_model->where("code='$value[B]'")->find()) {
                        $user_repeat++;
                        echo "重复的记录：";
                        var_dump("$value[B]");
                        echo "\r\n";
                    } else {
                        // 图片处理start
                        foreach ($image_info as $kk => $vv) {
                            $kk_new = substr($kk, -1);
                            if ($kk_new == $key) {
                                // 获取图片名字&拼接URL
                                $path_parts = pathinfo($vv);
                                $basename = $path_parts['basename'];
                                $ima = \think\Image::open($vv);
                                // 将图片裁剪为300x300并保存为crop.png
                                // $ima->crop(300, 300,100,30)->save(ROOT_PATH . "public/uploads/crop$kk.png");
                                $ima->thumb(600, 600)->save(ROOT_PATH . "public/uploads/teacher/$basename");
                                $full_image_path = SITE_URL . "teacher/" . "$basename";
                 
                                $img_id = Db::name('image')->insertGetId([
                                    "url" => "$full_image_path",
                                    'createdtime' => date("Y-m-d H:i:s"),
                                    'changedtime' => date("Y-m-d H:i:s")
                                ]);
                                $data['image'] = $img_id ? $img_id : 0;
                            }
                        }
                        // 图片处理end
                        // 处理带班
                        if ($value['F'] == '是') {
                            // $class_grade_info = $this->classGradeModel->where("remark = '$value[G]'")->find();
                            $class_grade_info = Db::name("class_grade")->where("remark = '$value[G]'")->find();
                            if ($class_grade_info) {
                                $data['c_g_id'] = $class_grade_info['id'];
                            } else {
                                return $this->error("班级名称不存在");
                            }
                        } else {
                            $data['c_g_id'] = 2;
                        }
                        $data['realname'] = empty($value['A']) ? 0 : $value['A'];
                        $data['code'] = empty($value['B']) ? 0 : $value['B'];
                        $data['gender'] = ($value['D'] == '男') ? 1 : 0;
                        $data['telphone'] = empty($value['E']) ? 0 : $value['E'];
                        $data['is_foreman'] = empty($value['F']) ? 2 : (($value['F'] == "是") ? 1 : 2);
                        $data['remark'] = empty($value['E']) ? 0 : $value['E'];
                        $data['profession'] = empty($value['H']) ? 0 : $value['H'];
                        $data['s_id'] = $s_id;
                        $data_2_arr[] = $data;
                    }
                }
                $teacher_id_new = $this->_model->saveAll($data_2_arr);
                if ($teacher_id_new) {
                    $sum++;
                } else {
                    $error_num++;
                }
                // } catch (\Exception $e) {
                // echo $e->getMessage();
                // // 事务回滚
                // // Db::rollback();
                //                 }
                echo "上传结束\r\n导入成功：". count($data_2_arr) .";\r\n重复总数：".$user_repeat . "\r\n失败条数：" . $error_num;die;
            } else {
                // 上传失败获取错误信息
                return $this->error($file->getError());
            }
        }
    }

    /**
     * 产品修改
     */
    public function product_edit()
    {
        if (Request::instance()->isPost())
        {
            
            $file = request()->file('cover');
            if(isset($file)){  
                $info = $file->move(ROOT_PATH . 'public/uploads');  
          
                if($info){  
                        // 成功上传后 获取上传信息  
                    $a=$info->getSaveName();  
                    $imgp= str_replace("\\","/",$a);  
                    $imgpath='uploads/'.$imgp;  
                    $_POST['cover']= $imgpath;
                    $re = Db::table('product')->where('id',input('param.id'))->update($_POST);
                    if ($re)
                    {
                        $this->success('修改成功','admin/product/index');
                    }else{
                        $this->error('修改失败');
                    }
                }else{
                    echo $file->getError(); 
                }
            }else{

                $re = Db::table('product')->where('id',input('param.id'))->update($_POST);
                if ($re)
                    {
                        $this->success('修改成功','admin/product/index');
                    }else{
                        $this->error('修改失败');
                    }
            }
        }
        $id = input('param.id');
        $detail = Db::table('product')->find($id);
        $this->assign('detail',$detail);
        return $this->fetch();
    }
    
    /**
     * 产品删除
     */
    public function product_del($id)
    {
    	if ($id) {
            $re = Db::table('product')->delete($id);
            if ($re) {
                return 1;
            }else{
                return 0;
            }
        }else{
            return 0;
        }

    }


}
