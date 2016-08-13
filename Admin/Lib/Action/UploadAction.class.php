<?php

class UploadAction extends Action{

    private $video_max = 300000000;
    private $video_dir = "./Public/Uploads/Video/";

    private $pic_max = 102400000; 
    private $pic_dir = "./Public/Uploads/Pic/";

    private $fujian_dir = "./Public/Uploads/Fujian/";

    private $imgarr = array(
        'mv' => 1,
        'fujian' => 2,
        'pic' => 0,
    );

    private $lnkurl ;
    public function uploadPic(){
        $upfield = $this->_param('photofield');
        $isdb = $this->_param('scroll');
        $title = $this->_param('title');
        $type = $this->_param('type');
        $data = $_FILES[$upfield];
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();// 实例化上传类
        $upload->maxSize  = $this->pic_max;// 设置附件上传大小
        $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->savePath =  $this->pic_dir;// 设置附件上传目录
        if(!$upload->upload()) {// 上传错误提示错误信息
            $this->error($upload->getErrorMsg());
        }else{// 上传成功 获取上传文件信息
            $info =  $upload->getUploadFileInfo();
            $lnk = $this->getLinkUrl($info[0]['savepath'],$info[0]['savename']);
            if($isdb == 1){
                $img_arr = array(
                    'imgs' => $lnk,
                    'type' => 0,
                    'title' => $title,
                    'ctime' => date('Y-m-d H:i:s'),
                );
                M('tb_scroll_img')->add($img_arr);
            }
            $this->ajaxReturn('ok',$lnk,1);
        }
    }
    public function uploadFujian(){
        $upfield = $this->_param('photofield');
        $isdb = $this->_param('isdb');
        $type = $this->_param('type');
        $data = $_FILES[$upfield];
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();// 实例化上传类
        $upload->maxSize  = $this->pic_max;// 设置附件上传大小
        $upload->allowExts  = array('doc','docx');// 设置附件上传类型
        $upload->savePath =  $this->fujian_dir;// 设置附件上传目录
        if(!$upload->upload()) {// 上传错误提示错误信息
            $this->error($upload->getErrorMsg());
        }else{// 上传成功 获取上传文件信息
            $info =  $upload->getUploadFileInfo();
            $lnk = $this->getLinkUrl($info[0]['savepath'],$info[0]['savename']);
            if($isdb == 1){
                $img_arr = array(
                    'imgs' => $lnk,
                    'type' => 2,
                    'title' => $data['name'],
                    'ctime' => date('Y-m-d H:i:s'),
                );
                $id = M('tb_scroll_img')->add($img_arr);
            }
            $this->ajaxReturn('ok',array('name'=>$data['name'],'url'=>$lnk,'id'=>$id),1);
        }
    }

    public function uploadVideo(){
        $upfield = $this->_param('photofield');
        $type = $this->_param('type');
        $data = $_FILES[$upfield];
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();// 实例化上传类
        $upload->maxSize  = $this->video_max; //
        $upload->allowExts  = array('mp4');// 设置附件上传类型
        $upload->savePath =  $this->video_dir;// 设置附件上传目录
        if(!$upload->upload()) {// 上传错误提示错误信息
            $this->error($upload->getErrorMsg());
        }else{// 上传成功 获取上传文件信息
            $info =  $upload->getUploadFileInfo();
            $lnk = $this->getLinkUrl($info[0]['savepath'],$info[0]['savename']);
            $this->ajaxReturn('ok',$lnk,1);
        }
        
    }

    public function getLinkUrl($fpath,$fname){
        #$server = C('HOST_ADMIN');
        $server = "http://".$_SERVER['HTTP_HOST'];
        $lnk = $server . trim($fpath,'.') .  $fname;
        return $lnk;
    }
}
