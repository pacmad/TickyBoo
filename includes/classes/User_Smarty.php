<?php
/**
%%%copyright%%%
 *
 * FusionTicket - ticket reservation system
 * Copyright (C) 2007-2008 Christopher Jenkins. All rights reserved.
 *
 * Original Design:
 *	phpMyTicket - ticket reservation system
 * 	Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
 *
 * This file is part of fusionTicket.
 *
 * This file may be distributed and/or modified under the terms of the
 * "GNU General Public License" version 2 as published by the Free
 * Software Foundation and appearing in the file LICENSE included in
 * the packaging of this file.
 *
 *
 * This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
 * THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE.
 *
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * Contact info@noctem.co.uk if any conditions of this licencing isn't 
 * clear to you.
 
 */

class User_Smarty {

  var $logged;

  function User_Smarty (&$smarty)
  {
    if(isset($_SESSION['_SHOP_USER'])){
      $user=$_SESSION['_SHOP_USER'];
      $this->_fill($user);
      $this->logged=true;
    }

    $smarty->register_object("user",$this);
    $smarty->assign_by_ref("user",$this);
    $smarty->assign("user_fields",array(
	    array('name'=>'user_id','mandatory'=>1,'short'=>0),
      array('name'=>'user_firstname','mandatory'=>1,'short'=>1),
      array('name'=>'user_lastname','mandatory'=>1,'short'=>1),
      array('name'=>'user_addresse','mandatory'=>1,'short'=>1),
      array('name'=>'user_addresse1','mandatory'=>0,'short'=>0),
      array('name'=>'user_zip','mandatory'=>1,'short'=>1),
      array('name'=>'user_city','mandatory'=>1,'short'=>1),
	    array('name'=>'user_state','mandatory'=>1,'short'=>1),
      array('name'=>'user_country','mandatory'=>1,'short'=>1),
      array('name'=>'user_email','mandatory'=>1,'short'=>0),
      array('name'=>'user_phone','mandatory'=>0,'short'=>0),
      array('name'=>'user_fax','mandatory'=>0,'short'=>0)
    ));
  }


  function login ($params,&$smarty){
  	if($id=$this->login_f($params['username'],$params['password'],$err)){
      if ($params['uri']){
		     echo "<script>window.location.href='{$params['uri']}';</script>"; exit;
      }
      else
      {
		     echo "<script>window.location.href='{$_SERVER["REQUEST_URI"]}';</script>"; exit;
      }
  	}else{
	  	$smarty->assign("login_error",$err);
	  }
	
  }

  function login_f ($username, $password, &$err){
    require_once('classes/ShopDB.php');
    
  	$sql="SELECT auth.username,User.*
              FROM auth,User
  	    	WHERE auth.user_id=User.user_id
  	      	AND username=".ShopDB::quote($username)."
  	      	AND password="._esc(md5($password))."
       	  	AND user_status=2
  		  	LIMIT 1";

  	if(!$res=ShopDB::query_one_row($sql)){
  		$err =con('log_err_wrong_usr');
  		return false;
  	}
  	$sql = "SELECT *
  		FROM auth,User
  		WHERE username=".ShopDB::quote($username)."
  		AND password="._esc(md5($password))."
  		AND user_status=2
  		AND User.user_id=".ShopDB::quote($res['user_id'])."
  		AND auth.user_id=User.user_id
  		AND auth.active IS NULL
  		LIMIT 1";

  	if(!$query=ShopDB::query($sql) and $error['error']) {
  		$err =con('log_err_not_act');
  		return FALSE;
  	}
  	if($res and $query){
	  	$res['is_member']=true;
    	$_SESSION['_SHOP_USER']=$res;
    	$this->_fill($res);
    	$this->logged=true;
    	$this->is_member=true;
    	return $res['user_id'];
  	}
		$err =con('log_err_unknown');
  	return FALSE;
  
  }

  function logout ($params,&$smarty){
    $this->logout_f();
  }

  function logout_f (){
    $this->_clean();
    unset($_SESSION['_SHOP_USER']);
  }
  
 /* User data gets subbmitted to here */ 
  function member ($params,&$smarty){
    if(!$this->member_f($params['data'],$err,$params['mandatory'], $params['secure'])){
      $smarty->assign('user_errors',$err);
    }
  }

/*The next bit of code creates users */
  function member_f (&$member,&$err,$login=TRUE,$mandatory_l=0, $secure=''){
    require_once('functions/user_func.php');

  	if(!empty($mandatory_l)){
  		if(preg_match_all('/\w+/',$mandatory_l,$matches)){
  			$mandatory=$matches[0];
  		}
  	}
//  	echo print_r($_SESSION);
  	
    If (!empty($secure)) {
      if (empty($member[$secure])) {
        $err[$secure] = mandatory;
        return 0;
      }
      elseif ($_SESSION['_NoSpam'][$secure] <> md5(strtoupper ($member[$secure]))) {
        $err[$secure] = invalid;
        return 0;
      }
    }

		
    if($res = create_member($member,$err,$mandatory)){ /* $res == the returned $user_id from create_member in user_func.php */
  	  header("location: {$_SERVER["PHP_SELF"]}?action=activate&register_user=on");
      return $res;
    }  
//    echo "error";
  }
///////////////////
//Update Member Function!
/////////////////////

  function update_member($params,&$smarty){
  	if(!$this->update_member_f($params['data'],$err,$params['mandatory'])){
		$smarty->assign('user_errors',$err);
	}
  }
  
  function update_member_f (&$member,&$err,$login=TRUE,$mandatory_l=0){
    require_once('functions/user_func.php');

		if(!empty($mandatory_l)){
		  if(preg_match_all('/\w+/',$mandatory_l,$matches)){
			  $mandatory=$matches[0];
			}
		}
		
    if($res = update_member2($member,$err,$mandatory)){ /* $res == the returned $user_id from create_member in user_func.php */
      $this->login_f($member['user_email'],$member['password1'],$err);
	  $_SESSION['_USER_UPDATED']="User Information updated successfully!";
	  //header("location: index.php?personal_page=details");
      return $res;
    }
	return false;
  }

///////////////////
///////Guest///////
///////////////////
// Guest Don't use this code anymore. Its for purchases without having to log on.
  function guest ($params,&$smarty){
    if(!$this->guest_f($params['data'],$err, $params['short'], $params['mandatory'], $params['secure'])){
      $smarty->assign('user_errors',$err);
    }
  }

  function guest_f (&$guest,&$err,$short=FALSE,$mandatory_l=0, $secure=''){
    require_once('functions/user_func.php');
		if(!empty($mandatory_l)){
		  if(preg_match_all('/\w+/',$mandatory_l,$matches)){
			  $mandatory=$matches[0];
			}
		}

//  	echo $_SESSION['_NoSpam'][$secure].' - '.md5(strtoupper ($guest[$secure]));

    If (!empty($secure)) {
      if (empty($guest[$secure])) {
        $err[$secure] = mandatory;
        return 0;
      }
      elseif ($_SESSION['_NoSpam'][$secure] <> md5(strtoupper ($guest[$secure]))) {
        $err[$secure] = invalid;
        return 0;
      }
    }

    if($guest_id = create_guest($guest,$err,$short,$mandatory)){
      $this->_login_guest($guest_id);
      return $guest_id;
    }  
  }
/////////////////
/////////////////  
  function forgot_password ($params,&$smarty){
    $this->forgot_password_f($params['email'],$params['template']);
  }

  function forgot_password_f ($email,$tpl=''){
    global $_SHOP;
  
    $pwd = substr(md5(uniqid(rand())), 0, 8);
    $pwd_md5=md5($pwd);
    
    
    
    $query="SELECT * from auth,User where username=".ShopDB::quote($email)." and auth.user_id=User.user_id";
    if(!$row=ShopDB::query_one_row($query)){
      return FALSE;
    }
    
    $query="UPDATE auth SET password="._esc($pwd_md5)." WHERE username=".ShopDB::quote($email)." limit 1";

    if(ShopDB::query($query) and shopDB::affected_rows()==1){
      require_once("classes/TemplateEngine.php");
      require_once("classes/htmlMimeMail.php");

      $engine= new TemplateEngine();
			if(empty($tpl)){
			  $tpl='forgot_passwd';
			}
      $tpl=$engine->getTemplate($tpl); //'forgot_passwd'
      $email=&new htmlMimeMail();

      $row['new_password']=$pwd;
      $tpl->build($email,$row);
 
      if($email->send($tpl->to)){
        return true;
      }  
    }
  }
  
  	function resend_activation($params,&$smarty){
  		$this->resend_activation_f($params['email'],$params['template']);
	}
	
	function resend_activation_f($email,$tpl=''){
		global $_SHOP;
		
		$query="SELECT * FROM auth,User WHERE username=".ShopDB::quote($email)." and auth.user_id=User.user_id";
		// Cheacks for a realy user.
	    if(!$row=ShopDB::query_one_row($query)){
	    	echo("#ERR-NOUSR");
    		return FALSE;
    	}
    	//Checks to see if allready activated, if has been activated then return.
    	if($row['active']==null){
    		echo("#ERR-ISACT");
			return FALSE;
		}
		
		$active = md5(uniqid(rand(), true));
		$user_id=$row['user_id'];
		$query="UPDATE `auth` SET active="._esc($active)." WHERE username=".ShopDB::quote($email)." LIMIT 1";
		
		if(ShopDB::query($query) and shopDB::affected_rows($_SHOP->link)==1){
			require_once("classes/TemplateEngine.php");
      		require_once("classes/htmlMimeMail.php");
      	
       		$email=$_POST['user_email'];
			$engine= new TemplateEngine();
				if(empty($tpl)){
					$tpl='Signup_email';
				}
			$tpl=$engine->getTemplate($tpl); // 'Signup_email';
			$email=&new htmlMimeMail();

			$link= $_SERVER["PHP_SELF"]."?register_user=on&action=activate&x=".$user_id."&y=".$active;
			$row['link']=$link;
			$tpl->build($email,$row);
 
			if($email->send($tpl->to)){
				return TRUE;
			}else{
				echo("#ERR-SNDERR");
				return FALSE;
			}
		}
	}
  
  function _fill ($user){ ///????
    $this->_clean();
    foreach($user as $k=>$v){
      $this->$k=$v; /// What does this do? Sets User_Smary->$k as $v ?
    }
  }

  function _clean (){
    $user=(array)$this;
    foreach($user as $k=>$v){
      unset($this->$k);
    }
  }
  
  	function login_guest($params,&$smarty){
		if(!$this->_login_guest($params['user_id'])){
			return;
		}
	}

  function _login_guest ($user_id){
    require_once('classes/ShopDB.php');
    $query="SELECT * 
			FROM `User` 
	    	WHERE user_id=".ShopDB::quote($user_id)." 
	      	AND user_status=3
	    	LIMIT 1";
      

    if($result=ShopDB::query($query) and $user=shopDB::fetch_assoc($result)){
      $user['is_guest']=true;
      $_SESSION['_SHOP_USER']=$user;
      $this->_fill($user);
      $this->logged=true;
      $this->is_guest=true;
      return $user['user_id'];
    }
    
    return FALSE;
  
  }

  function Activate($params,&$smarty){

    if (isset($_GET['x'])) {
        $x = (int) $_GET['x'];
    } else {
        $x = 0;
    }
    if (isset($_GET['y'])) {
        $y = $_GET['y'];
    } else {
        $y = 0;
    }

    if ( ($x> 0) && (strlen($y) == 32)) {

        $query = "UPDATE auth SET active=NULL WHERE (user_id="._esc($x)." AND active='" . _esc($y) . ") LIMIT 1";

        if ($result = ShopDB::query($query) and shopDB::affected_rows() == 1) {
            return act_oke;
        } else {
            return '<p><font color="red" size="+1">'.act_error.'</font></p>';
        }
        shopDB::close($result);
    } else {
        return "<b>".act_uselink."</b>";
    }
  }

}
?>