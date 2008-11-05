<?php
/*
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
 * Licencees holding a valid "phpmyticket professional licence" version 1
 * may use this file in accordance with the "phpmyticket professional licence"
 * version 1 Agreement provided with the Software.
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

require_once("classes/MyCart.php");

class MyCart_Smarty {
  var $error='';
  
  function MyCart_Smarty (&$smarty){
    $smarty->register_object("cart",$this,null,true,array("items"));
    $smarty->assign_by_ref("cart",$this);
    
  }
  
  
  function is_empty_f () {
    $cart=$_SESSION['cart'];
    return !isset($cart) or $cart->is_empty();
  }

  function is_empty ($params,&$smarty) {
    return $this->is_empty_f();
  }



  function total_seats_f ($event_id,$category_id,$only_valid){
    $cart=$_SESSION['cart'];
    
    if($cart){
      return $cart->total_places($event_id,$category_id,$only_valid);
    }else{
      return 0;
    }
  }

  function total_seats ($params,&$smarty){
    return $this->total_seats_f($params['event_id'],$params['category_id'],$params['only_valid']);
  }



  function add_item ($params, &$smarty){
    if (!$this->add_item_f($params['event_id'],$params['category_id'],$params['seats'],$params['mode'])){
      $smarty->assign("cart_error",$this->error);
    }
  }

  function add_item_f ($event_id,$category_id,$seats,$mode='mode_web',$reserved=false){
    if(!$mode){
      $mode='mode_web';
    }
    $res=$this->CartCheck($event_id,$category_id,$seats,$mode,$reserved);
    if($res){
      return $res;
    }else{
      return FALSE;
    }
  }


  function remove_item ($params, &$smarty){
    $this->remove_item_f($params['event_id'],$params['category_id'],$params['item_id']);
  }

  function remove_item_f ($event_id, $cat_id, $item_id){
    if($cart=$_SESSION['cart']){

      if($places=$cart->remove_place($event_id,$cat_id,$item_id)){
        require_once('classes/Seat.php');
        Seat::free(session_id(),$event_id,$cat_id,$places);
      }
      
      $_SESSION['cart']=$cart;
    }  
  }
  
  function total_price ($params, &$smarty){
    return $this->total_price_f();
  }

  function total_price_f (){
    if($cart=$_SESSION['cart']){
      return $cart->total_price();
    }
  }
  
  	function use_alt ($params, &$smarty){
    	return $this->use_alt_f();
  	}

  	function use_alt_f (){
    	if($cart=$_SESSION['cart']){
      		return $cart->use_alt();
    	}
  	}
  	function min_date_f (){
		if($cart=$_SESSION['cart']){
      		return $cart->min_date();
    	}
  	}
  
  function can_checkout ($params, &$smarty){
    return $this->can_checkout_f();
  }

  function can_checkout_f (){
    if($cart=$_SESSION['cart']){
      return $cart->can_checkout();
    }
  }

  function overview ($params, &$smarty){
    return $this->overview_f();
  }

  function overview_f (){
    if($cart=$_SESSION['cart']){
      return $cart->overview();
    }
  }


  
  function items ($params, $content, &$smarty, &$repeat){
    if($repeat){
      $cart=$_SESSION['cart'];
      //print_r($cart);

      if(!$cart or $cart->is_empty()){
        $repeat=FALSE;
        return;
      }else{
        $cart->load_info();
      }
      
      $this->cart_list=array();
      $this->cart_index=0;  

      $cart->iterate(array(&$this,'_pre_items'),$this->cart_list);

    }
    
    if($cart_row=&$this->cart_list[$this->cart_index++]){
      $smarty->assign_by_ref("event_item",$cart_row[0]);
      
      
      $smarty->assign_by_ref("category_item",$cart_row[1]);
      
      
      
      $seat_item=$cart_row[2];

      $smarty->assign_by_ref("seat_item",$seat_item);
      $smarty->assign("seat_item_id",$seat_item->id);
      $smarty->assign("seats_id",$seat_item->places_id);
      $smarty->assign("seats_nr",$seat_item->places_nr);

      $cat= $cart_row[1];
      if($cat->cat_numbering=='rows'){
        $rcount=array();
        foreach($seat_item->places_nr as $places_nr){
          $rcount[$places_nr[0]]++;
	}
        $smarty->assign("seat_item_rows_count",$rcount);
      }
      
      $repeat=TRUE;
      
    }else{
      $repeat=FALSE;
    }
    
    return $content;
  }

  function _pre_items (&$event_item,&$cat_item,&$place_item,&$data){
    $data[]=array($event_item,$cat_item,$place_item);
  }


  function destroy_f (){
    unset($_SESSION['cart']);  
  }

  function destroy ($params,&$smarty){
    unset($_SESSION['cart']);  
  }

  function set_discounts ($params,&$smarty){

    $this->set_discounts_f($params['event_id'],$params['category_id'],$params['item_id'],$params['discounts']);
  }
  
  function set_discounts_f ($event_id,$category_id,$item_id,$discounts){
    if(!$cart=$_SESSION['cart']){return;}

    require_once("classes/Discount.php");
        
    foreach($discounts as $disc_id){
      if($disc_id>0){
        if(!isset($dcache[$disc_id])){
          $dcache[$disc_id]=Discount::load($disc_id);
        }
        $discs[]=$dcache[$disc_id];
	      $has=1;
      }else{
        $discs[]=0;
      } 	
    }
  
    if($has){ 
      if($cart->set_discounts($event_id,$category_id,$item_id,$discs)){
        $_SESSION['cart']=$cart;
	      return TRUE;
      }
    }  

  }

  function CartCheck ($event_id,$category_id,$places,$mode='mode_web',$reserved){

    require_once ("classes/Seat.php");
    require_once ("classes/Event.php");
    require_once ("classes/Category.php");

  	// Loads event details
    if(!$event=Event::load($event_id)){
      return FALSE;
    }
    // Loads cat details
    if(!$category=Category::load($category_id)){
      return FALSE;
    }

    //checks the seating numbering.
    if($category->category_numbering=='none'){
      if(!($places>0)){
        $this->error=places_empty;
        return FALSE;
      }
      $newp = $this->places;
    }else if($category->category_numbering=='rows' or
             $category->category_numbering=='both' or
	           $category->category_numbering=='seat')
    {
      if(!is_array($places) or empty($places)){
        $this->error=places_empty;
        return FALSE;
      }
      $newp = count($places);
    }else{
      user_error("unknown: category_numbering '{$category->category_numbering}' category_id '{$category->category_id}'");
      return FALSE;
    }

    $max=$event->event_order_limit;

    $cart=$_SESSION['cart'];

    if($mode=='mode_web' and $max){
      if(isset($cart)){

        $has = $cart->total_places($this->event_id);
        if(($has+$newp)>$max){
          $this->error = event_order_limit_exceeded;
      	  return FALSE;
      	}
      }else if($newp>$max){
        $this->error = event_order_limit_exceeded;
        return FALSE;
      }
    }
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if($places_id=Seat::reservate(session_id(), $event_id, $category_id, $places, $category->category_numbering, $reserved)){

	  //if cart empty create new cart
      if(!isset($cart)){
        $cart=new Cart();
      }

      // add place in cart.
      $res=$cart->add_place($event_id, $category_id, $places_id);

      $cart->load_info();

      $_SESSION['cart']=$cart;
      
      return $res;

    }else{
      global $_SHOP;
      if(is_array($_SHOP->place_error)){
        switch($_SHOP->place_error['errno']){
      	  case PLACE_ERR_OCCUPIED:
      	    $this->error=places_occupied;
      	    break;
      	  case PLACE_ERR_TOOMUCH:
      	    $this->error=places_toomuch;
      	    if($this->mode=='mode_kasse'){
      	      $this->error.=places_remains.": ".$_SHOP->place_error['remains'];
      	    }
      	    break;

      	  case PLACE_ERR_INTERNAL:
      	  default:
      	    $this->error=internal_error.' ['.$_SHOP->place_error['place'].'] '. $_SHOP->db_error;
      	    break;
      	}
      }else{
        $this->error=internal_error.' ['.$_SHOP->place_error['errno'].']'. print_r($_SHOP->place_error, true);
      }

      return FALSE;
    }
  }

}

?>