<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Page;
class UserController extends Controller
{
	public function UserList()
	{
		$p=I('get.p');
		if ($p=="") {
			$p=1;
		}
		$user=M('users');
		$tbuser=I('get.TbUserID');
		$count=$user->where("username like '%".$tbuser."%' or userid like '%".$tbuser."%'")
					->count();
		$page=new Page($count,C('PAGE_SIZE'));
		$page->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条记录&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
	    $page->setConfig('prev', '上一页');
	    $page->setConfig('next', '下一页');
	    $page->setConfig('last', '末页');
	    $page->setConfig('first', '首页');
	    $page->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
	    $page->lastSuffix = false;//最后一页不显示为总页数
		$list=$user->where("username like '%".$tbuser."%' or userid like '%".$tbuser."%'")
				   ->order('uptime desc')
				   ->page($p.','.C('PAGE_SIZE'))
				   ->select();
		$this->assign('page',$page->show());
		$this->assign('userlist',$list);
		$this->assign('TbUserID',$tbuser);
		$this->display('userlist');
	}

	public function deleteuser()
	{
		$userid=I('userid');
		$data["p"]=I('p');
		$data["tbuserid"]=I('tbuserid');
		if (IS_AJAX) {
			# code...
			$user=M('users');
			if ($user->where("userid='".$userid."'")->delete()) {
				$data["info"]="删除成功";
			}
			else
			{
				$data["info"]="删除失败";
			}
		}
		else
		{
			$data["info"]="非法操作";
		}
		$data["url"]=U('user/userlist',array('p'=>$data["p"],'TbUserID'=>$data["tbuserid"]));
		$this->ajaxReturn($data);
	}

	public function restuserpwd()
	{
		$userid=I('userid');
		$data["p"]=I('p');
		$data["tbuserid"]=I('tbuserid');
		if (IS_AJAX) {
			$user=M('users');
			$pwd=md5('123');
			if ($user->where("userid='".$userid."'")->setField('USERPASSWORD',$pwd)) {
				$data["info"]="密码重置成功，新密码为123";
			}
			else
			{
				$data["info"]="密码重置失败";
			}
		}
		else
		{
			$data["info"]="非法操作";
		}
		$data["url"]=U('user/userlist',array('p'=>$data["p"],'TbUserID'=>$data["tbuserid"]));
		$this->ajaxReturn($data);
	}

	public function main()
    {
        $UserID=session("userid");
        $UserName=session("username");
        $userinrole=D('Userinrole');
        $UserRoles=$userinrole->ShowUserRoles($UserID);
        $this->assign('UserID',$UserID);
        $this->assign('UserName',$UserName);
        $this->assign('UserRoles',$UserRoles);
        $this->display('main');
    }

	public function updpwd()
    {
        $this->display('updpwd');
    }

    public function doupdpwd()
    {
        if (IS_POST) {
            $oldpwd = I('post.oldpwd');
            $userpwd = I('post.userpwd');
            $user= D('users');
            $userid=session("userid");
            //1为修改成功 ，2为密码错误，3为用户名错误
            $flag=$user->updpwd($userid,$oldpwd,$userpwd);
            switch ($flag) {
                case '1':
                    $this->success('修改成功',U('user/updpwd'),3);
                    break;
                case '2':
                    $this->error('密码错误',U('user/updpwd'),3);
                    break;
                case '3':
                    $this->error('用户名错误',U('user/updpwd'),3);
                    break;
                default:
                    break;
            }
        }
        else
        {
            $this->error('非法操作',U('user/updpwd'),5);
        }
    }

    public function edit()
    {
    	# code...
    	$userid=I('get.userid');
    	$this->userid=$userid;
    	$this->title="编辑用户信息";
    	$user=M('users');
    	$row=$user->field('userid,username')
    			  ->where("userid='".$userid."'")
    			  ->find();
    	if ($row) {
    		$this->username=$row["username"];
    	}
    	$roles=M('roles');
    	$rolerows=$roles->field("roleid,rolename,'' as ischecked")
    					->order('ordernum')
    					->select();
    	$userrole=M('userinrole');
    	$userrolerows=$userrole->field('roleid')
    						   ->where("userid='".$userid."'")
    						   ->select();
    	for ($i=0; $i <count($rolerows) ; $i++) {
    		for ($j=0; $j <count($userrolerows) ; $j++) {
    			if ($rolerows[$i]["roleid"]==$userrolerows[$j]["roleid"]) {
    				$rolerows[$i]["ischecked"]="checked='checked'";
    				break;
    			}
    		}
    	}
    	$this->rolelist=$rolerows;
    	$this->display('useredit');

    }
}
?>