<?php

// +----------------------------------------------------------------------
// | JiZhiCMS { 极致CMS，给您极致的建站体验 }  
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://www.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/01-2019/02
// +----------------------------------------------------------------------


namespace A\c;

use FrPHP\lib\Controller;
use FrPHP\Extend\Page;

class ExtmoldsController extends CommonController
{
	function _init(){
		if(!isset($_SESSION['admin']) || $_SESSION['admin']['id']==0){
      	  Error('请登录账户~',U('Login/index'));
        
		}
		
		if($_SESSION['admin']['isadmin']!=1){
			if(strpos($_SESSION['admin']['paction'],','.APP_CONTROLLER.',')==false){
				$molds = $this->frparam('molds',1);
				$action = APP_CONTROLLER.'/'.APP_ACTION.'/molds/'.$molds;
				
				if(strpos($_SESSION['admin']['paction'],','.$action.',')==false){
				   $ac = M('Ruler')->find(array('fc'=>$action));
				  
				   Error('您没有'.$ac['name'].'的权限！');
				}
			}
		   
		  
		}
		
		  $webconf = webConf();
		  $template = get_template();
		  $this->webconf = $webconf;
		  $this->template = $template;
		  $this->tpl = Tpl_style.$template.'/';
		  $customconf = get_custom();
		  $this->customconf = $customconf;
		
	}
	public function index(){
		
		$classtypedata = classTypeData();
		foreach($classtypedata as $k=>$v){
			$classtypedata[$k]['children'] = get_children($v,$classtypedata);
		}
		$molds = $this->frparam('molds',1);
		if($molds==''){
			Error('模块为空，请选择模块！');
		}
		$this->molds = M('Molds')->find(array('biaoshi'=>$molds));
		$data = $this->frparam();
		$res = molds_search($molds,$data);
		$sql = '1=1';
		if($this->frparam('isshow')){
			$isshow = $this->frparam('isshow')==1 ? 1 : 0;
			$sql .= ' and isshow='.$isshow;
		}
		$this->isshow = $this->frparam('isshow');
		$get_sql = ($res['fields_search_check']!='') ? (' and '.$res['fields_search_check']) : '';
		$sql .= $get_sql;
		
		if($this->frparam('tid')){
			$sql .= ' and tid in('.implode(",",$classtypedata[$this->frparam('tid')]["children"]["ids"]).')';
			//$sql .= ' and tid='.$this->frparam('tid');
		}
		$this->tid = $this->frparam('tid');
		
		//$sql = ($res['fields_search_check']!='')?$res['fields_search_check']:null;
		$this->fields_search = $res['fields_search'];
		$page = new Page($molds);
		
		$data = $page->where($sql)->orderby('orders desc,id desc')->page($this->frparam('page',0,1))->go();
		$pages = $page->pageList();
		$this->pages = $pages;
		$this->lists = $data;
		$this->sum = $page->sum;
		$this->fields_list = M('Fields')->findAll(array('molds'=>$molds,'islist'=>1),'orders desc,id asc');
		
		$classtype = M('classtype')->findAll(array('molds'=>$molds),'orders desc');
		//$classtype = getTree($classtype);
	
		$this->classtypes = $classtype;
		
		
		$this->display('extmolds-list');
		
	}
	
	public function addmolds(){
		$molds = $this->frparam('molds',1);
		$this->fields_biaoshi = $molds;
		if($this->frparam('go',1)==1){
			
			$data = $this->frparam();
			if(isset($data['tid'])){
				$pclass = get_info_table('classtype',array('id'=>$data['tid']));
				$data['htmlurl'] = $pclass['htmlurl'];
			}
			
			$data = get_fields_data($data,$molds);
			if(M($molds)->add($data)){
				
				JsonReturn(array('code'=>0,'msg'=>'添加成功！'));
				
			}else{
				
				JsonReturn(array('code'=>1,'msg'=>'添加失败！'));
				
			}
			
			
			
		}
		$classtype = M('classtype')->findAll(array('molds'=>$molds),'orders desc');
		$classtype = set_class_haschild($classtype);
		$classtype = getTree($classtype);
		
		$this->classtypes = $classtype;
		$this->tid=  $this->frparam('tid');
		$this->molds = M('Molds')->find(array('biaoshi'=>$molds));
		
		$this->display('extmolds-add');
	}
	
	public function editmolds(){
		$molds = $this->frparam('molds',1);
		$this->fields_biaoshi = $molds;
		if($this->frparam('go',1)==1){
			
			$data = $this->frparam();
			if(isset($data['tid'])){
				$pclass = get_info_table('classtype',array('id'=>$data['tid']));
				$data['htmlurl'] = $pclass['htmlurl'];
			}
			$data = get_fields_data($data,$molds);
			if($this->frparam('id')){
				if(M($molds)->update(array('id'=>$this->frparam('id')),$data)){
				
					JsonReturn(array('code'=>0,'msg'=>'修改成功！'));
					
				}else{
					
					JsonReturn(array('code'=>1,'msg'=>'修改失败！'));
					
				}
			
			}else{
				JsonReturn(array('code'=>1,'msg'=>'缺少ID'));
				
			}
			
			
			
		}
		$this->data = M($molds)->find(array('id'=>$this->frparam('id')));
		$this->molds = M('Molds')->find(array('biaoshi'=>$molds));
		//$classtype = M('classtype')->findAll(array('molds'=>$molds),'orders desc');
		//$classtype = set_class_haschild($classtype);
		//$classtype = getTree($classtype);
		$this->classtypetree =  get_classtype_tree();
		$this->classtypes = $this->classtypetree;
		$this->display('extmolds-edit');
	}
	
	public function  copymolds(){
		$id = $this->frparam('id');
		$molds = $this->frparam('molds',1);
		if($id){
			$data = M($molds)->find(['id'=>$id]);
			unset($data['id']);
			if(M($molds)->add($data)){
				
				JsonReturn(array('code'=>0,'msg'=>'复制成功！'));
				exit;
			}else{
				
				JsonReturn(array('code'=>1,'msg'=>'复制失败！'));
				exit;
			}
			
			
		}
	}
	
	//批量删除
	function deleteAll(){
		$data = $this->frparam('data',1);
		$molds = $this->frparam('molds',1);
		if($data!=''){
			if(M($molds)->delete('id in('.$data.')')){
				JsonReturn(array('code'=>0,'msg'=>'批量删除成功！'));
				
			}else{
				JsonReturn(array('code'=>1,'msg'=>'批量操作失败！'));
			}
		}
	}
	//单一删除
	function deletemolds(){
		$id = $this->frparam('id');
		$molds = $this->frparam('molds',1);
		if($id){
			if(M($molds)->delete('id='.$id)){
				
				JsonReturn(array('code'=>0,'msg'=>'删除成功！'));
			}else{
				
				JsonReturn(array('code'=>1,'msg'=>'删除失败！'));
			}
		}
	}
	
		//修改排序
	function editOrders(){
		$w['orders'] = $this->frparam('orders');
		$molds = $this->frparam('biaoshi',1);
		$r = M($molds)->update(array('id'=>$this->frparam('id')),$w);
		if(!$r){
			JsonReturn(array('code'=>1,'info'=>'修改失败！'));
		}
		JsonReturn(array('code'=>0,'info'=>'修改成功！'));
	}
	
}