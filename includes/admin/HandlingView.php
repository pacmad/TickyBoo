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
require_once("classes/ShopDB.php");
require_once("admin/AdminView.php");
require_once("classes/Organizer.php");
require_once("classes/Handling.php");

class HandlingView extends AdminView{


function print_select_tpl ($name,$type,&$data,&$err){
  global $_SHOP;

  $query="SELECT template_name FROM Template WHERE template_type='{$type}' and template_organizer_id='{$_SHOP->organizer_id}' ORDER BY template_name";
  if(!$res=ShopDB::query($query)){
    return FALSE;
  }

  $sel[$data[$name]]=" selected ";

  echo "<tr><td class='admin_name'  width='40%'>".$this->con($name)."</td>
  <td class='admin_value'>
   <select name='$name'>
   <option value=''></option>\n";

  while($v=shopDB::fetch_row($res)){
    $value=htmlentities($v[0],ENT_QUOTES);
    echo "<option value='$value' ".$sel[$v[0]].">{$v[0]}</option>\n";
  }

  echo "</select><span class='err'>{$err[$name]}</span>
  </td></tr>\n";
}

  function handling_check (&$data, &$err){
   global $_SHOP;
   if(empty($data['handling_pdf_template'])){$err['handling_pdf_template']=mandatory;}
   if($data['handling_sale_mode_a']){
		 $data['handling_sale_mode']=implode(',',$data['handling_sale_mode_a']);
	 }

	 $this->save_paper_format('pdf_paper',$data,$err);
	 $this->extra_check($data,$err);

   return empty($err);

  }

  function handling_view (&$data){
		echo "<table class='admin_form' width='$this->width' cellspacing='1' cellpadding='4'>\n";
		echo "<tr><td class='admin_list_title' colspan='2'>".view_handling."</td></tr>";
		$this->print_field('handling_id',$data);
		echo "<tr><td class='admin_name'>".$this->con(handling_payment)."</td>
		<td class='admin_value'>".$this->con($data['handling_payment'])."</td></tr>";
		echo "<tr><td class='admin_name'>".$this->con(handling_shipment)."</td>
		<td class='admin_value'>".$this->con($data['handling_shipment'])."</td></tr>";
		$this->print_field('handling_fee_fix',$data);
		$this->print_field('handling_fee_percent',$data);


		$data['handling_sale_mode']=str_replace('sp','POS',$data['handling_sale_mode']);
		$data['handling_sale_mode']=str_replace('www','WEB',$data['handling_sale_mode']);

		$this->print_field('handling_sale_mode',$data);
		$this->print_field('handling_pdf_template',$data);
		$this->print_field('handling_pdf_ticket_template',$data);
		$temps=explode(",",$data['handling_email_template']);
		foreach($temps as $temp){
			$t=explode("=",$temp);
			$data["handling_email_template_{$t[0]}"]=$t[1];
		}
		$this->print_field('handling_email_template_ord',$data);
		$this->print_field('handling_email_template_send',$data);
		$this->print_field('handling_email_template_payed',$data);
		$this->print_field('handling_text_payment',$data);
		$this->print_field('handling_text_shipment',$data);
		$this->print_field('handling_html_template',$data);

		$this->extra_view($data);

		echo "</table>\n";
		echo "<br><center><a class='link' href='{$_SERVER['PHP_SELF']}'>".admin_list."</a></center>";

  }


  function handling_form (&$data,&$err,$title){
		global $_SHOP;

		$h = new Handling();
		echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'>\n";
		echo "<table class='admin_form' width='$this->width' cellspacing='1' cellpadding='4'>\n";
		echo "<tr><td class='admin_list_title' colspan='2'>".$title."</td></tr>";

		if($data['handling_id']){
			echo "<tr><td class='admin_name'>".$this->con(handling_payment)."</td>
			<td class='admin_value'>".$this->con($data['handling_payment'])."</td></tr>";
			echo "<tr><td class='admin_name'>".$this->con(handling_shipment)."</td>
			<td class='admin_value'>".$this->con($data['handling_shipment'])."</td></tr>";
		}else{
			$sel[$data["handling_payment"]]=" selected ";
			echo "<tr><td class='admin_name'  width='40%'>".$this->con(handling_payment)."</td>
   	  <td class='admin_value'> <select name='handling_payment'>";
			$pay=$h->get_payment();

			foreach($pay as $k=>$v){
				 echo "<option value='$v' ".$sel[$v].">".$this->con($v)."</option>\n";
			}
			echo "</select><span class='err'>{$err["handling_payment"]}</span></td></tr>\n";


			$sel[$data["handling_shipment"]]=" selected ";

			echo "<tr><td class='admin_name'  width='40%'>".$this->con(handling_shipment)."</td>
			<td class='admin_value'><select name='handling_shipment'>";
			$send=$h->get_shipment();
			foreach($send as $k=>$v){
				echo "<option value='$v' ".$sel[$v].">".$this->con($v)."</option>\n";
			}
			echo "</select><span class='err'>{$err["handling_shipment"]}</span></td></tr>\n";
		}
		//This is for the alt payments if nothing is slected alt wont be used when close to event.
		$sel[$data["handling_alt"]]=" selected ";
		echo "<tr><td class='admin_name'  width='40%'>Alt Handling Method</td>
		<td class='admin_value'><select name='handling_alt'>";
		echo "<option value='1' ".$sel[1].">No Alt</option>\n";
		echo $h->get_handlings($data["handling_alt"]);
		echo "</select><span class='err'>{$err["handling_alt"]}</span></td></tr>\n";

		//This to ask if the handling is alturnative only this could be an auto proccess but then you would only be
		//able to use the handling when close to the event.
		$sel[$data["handling_alt_only"]]=" selected ";
		echo "<tr><td class='admin_name'  width='40%'>Only Alt Handling Method</td>
		<td class='admin_value'><select name='handling_alt_only'>";
		echo "<option value='No' ".$sel['No'].">No</option>\n";
		echo "<option value='Yes' ".$sel['Yes'].">Yes</option>\n";
		echo "</select><span class='err'>{$err["handling_delunpaid"]}</span></td></tr>\n";


		$sel[$data["handling_delunpaid"]]=" selected ";
		echo "<tr><td class='admin_name'  width='40%'>Delete Unpaid Payments</td>
		<td class='admin_value'><select name='handling_delunpaid'>";
		echo "<option value='No' ".$sel['No'].">No</option>\n";
		echo "<option value='Yes' ".$sel['Yes'].">Yes</option>\n";
		echo "</select><span class='err'>{$err["handling_delunpaid"]}</span></td></tr>\n";

		$this->print_input('handling_expires_min',$data,$err,10);
		$this->print_input('handling_fee_fix',$data,$err,5,10);
		$this->print_input('handling_fee_percent',$data,$err,5,10);


	#  if($data['handling_sale_mode']){
	#    echo "<tr><td class='admin_name'>".$this->con(handling_sale_mode)."</td>
	#    <td class='admin_value'>".$data['handling_sale_mode']."</td></tr>";
	#  }else{
		//if 'sp' is present set the tick box ticked same fore www.
		if(strpos($data['handling_sale_mode'],'sp')!==false){
			$chk_sp='checked';
		}

		if(strpos($data['handling_sale_mode'],'www')!==false){
			$chk_www='checked';
		}

		echo "<tr><td class='admin_name'>".$this->con(handling_sale_mode)."</td>
			<td class='admin_value'><input type='checkbox' name='handling_sale_mode_a[]' value='sp' $chk_sp>
			".$this->con(sp)."&nbsp;
			<input type='checkbox' name='handling_sale_mode_a[]' value='www' $chk_www>
			".$this->con(www)."</td></tr>";

	#  }
		$this->print_select_tpl('handling_pdf_template','pdf',$data,$err);
		$this->print_select_tpl('handling_pdf_ticket_template','pdf',$data,$err);
		$this->print_paper_format('pdf_paper',$data,$err);

		$temps=explode(",",$data['handling_email_template']);
		foreach($temps as $temp){
			$t=explode("=",$temp);
			$data["handling_email_template_{$t[0]}"]=$t[1];
		}

		$this->print_select_tpl('handling_email_template_ord','email',$data,$err);
		$this->print_select_tpl('handling_email_template_send','email',$data,$err);
		$this->print_select_tpl('handling_email_template_payed','email',$data,$err);


		if($data['handling_id']){
			$this->print_large_area('handling_text_payment',$data,$err,3,92,'');
			$this->print_large_area('handling_text_shipment',$data,$err,3,92,'');
			$this->print_large_area('handling_html_template',$data,$err,20,95,'',"class='codepress html'");
		  $this->extra_form($data,$err);
		}

		if($data['handling_id']){
			echo "<input type='hidden' name='handling_payment' value='{$data['handling_payment']}'/>\n";
			echo "<input type='hidden' name='handling_shipment' value='{$data['handling_shipment']}'/>\n";
			echo "<input type='hidden' name='handling_id' value='{$data['handling_id']}'/>\n";
			echo "<input type='hidden' name='action' value='update'/>\n";
		}else{
			echo "<input type='hidden' name='action' value='insert'/>\n";
		}

		echo "<tr><td align='center' class='admin_value' colspan='2'>
			<input type='submit' name='submit' value='".save."'>
		<input type='reset' name='reset' value='".res."'></td></tr>";

		echo "</table></form>\n";

		echo "<center><a class='link' href='{$_SERVER['PHP_SELF']}'>".admin_list."</a></center>";
  }

  function handling_list (){
		global $_SHOP;
		$pay=Handling::get_payment ();
		$send=Handling::get_shipment();
		$alt=0;
		echo "<table class='admin_list' width='$this->width' cellspacing='1' cellpadding='4'>\n";
		echo "<tr><td class='admin_list_title' colspan='8' align='center'>".handling_title."</td></tr>\n";
		if($hands=Handling::load_all()){
			foreach($hands as $hand){

				$handling_sale_mode=str_replace('sp','pos',$hand->handling_sale_mode);
				$handling_sale_mode=trim(str_replace('www','web',$handling_sale_mode));

				echo "<tr class='admin_list_row_$alt'>";
				if($hand->handling_id==1){
				 	echo  "<td  class='admin_list_item'>".reserved."</td>";
				 	echo "<td class='admin_list_item'>".reserved."</td>\n";
				 	echo "<td class='admin_list_item' colspan=3>&nbsp;</td>";
				}else{
					echo  "<td  class='admin_list_item'>".$this->con($hand->handling_payment)."</td>";
					echo "<td class='admin_list_item'>".$this->con($hand->handling_shipment)."</td>\n";
  				echo "<td class='admin_list_item' align='right'>";
    				$perc=$hand->handling_fee_percent;
    				$fixe=$hand->handling_fee_fix;
    				if($perc > 0 ){
    					echo $perc." % ";
    				}
    				if ($perc >0 and $fixe >0 ){
    					echo "+";
    				}
    				if($fixe > 0){
    					echo $fixe." ".$_SHOP->organizer_data->organizer_currency;
    				}
  				echo "</td>\n";
  				echo "<td class='admin_list_item'>$handling_sale_mode</td>\n";
  				echo "<td class='admin_list_item' width='60' align='right'>
                  <a class='link' href='view_handling.php?action=view&handling_id={$hand->handling_id}'><img src='images/view.png' border='0' alt='".view."' title='".view."'></a>\n
                  <a class='link' href='view_handling.php?action=edit&handling_id={$hand->handling_id}'><img src='images/edit.gif' border='0' alt='".edit."' title='".edit."'></a>\n
  				        <a class='link' href='javascript:if(confirm(\"".delete_item."\")){location.href=\"view_handling.php?action=remove&handling_id={$hand->handling_id}\";}'><img src='images/trash.png' border='0' alt='".remove."' title='".remove."'></a></td>";
			 	}
				echo "</tr>";
				$alt=($alt+1)%2;
			 }
		 }


		echo "</table>\n";

		echo "<br><center><a class='link' href='{$_SERVER['PHP_SELF']}?action=add'>".add."</a></center>";
  }

  function draw (){
	global $_SHOP;
	  if($_GET['action']=='view' and $_GET['handling_id']>0){
	    $hand=Handling::load($_GET['handling_id']);
	    $hand_a=(array)$hand;
	    $this->handling_view($hand_a);
	  }elseif($_GET['action']=='remove' and $_GET['handling_id']>0){
	    $hand=new Handling();
	    $hand->handling_id=$_GET['handling_id'];
	    $hand->delete();
	    $this->handling_list();
	  }elseif($_GET['action']=='edit'){
	    $hand=Handling::load($_GET["handling_id"]);
	    $hand_a=(array)$hand;
	    $this->handling_form($hand_a,$err,payment_update_title);
	  }elseif($_POST['action']=='update'){
	    if(!$this->handling_check($_POST,$err)){
	      $this->handling_form($_POST,$err,handling_update_title);
	      return 0;
	  	}
	  	$hand=new Handling();
	  	$hand->_fill($_POST);
	  	$hand->templates['ord']=$_POST['handling_email_template_ord'];
	  	$hand->templates['send']=$_POST['handling_email_template_send'];
	  	$hand->templates['payed']=$_POST['handling_email_template_payed'];

	  	$this->extra_fill($hand,$_POST);
	  	$hand->save();

	  	$this->handling_list();
	  	// adding new payments here then they are compiled.
	  }elseif($_POST['action']=='insert'){
	    if(!$this->handling_check($_POST,$err)){
	      $this->handling_form($_POST,$err,handling_add_title);
	    }else{
	      $hand=new Handling();
	      $hand->_fill($_POST);
	      $hand->templates['ord']=$_POST['handling_email_template_ord'];
	      $hand->templates['send']=$_POST['handling_email_template_send'];
	      $hand->templates['payed']=$_POST['handling_email_template_payed'];

	  	  //Adds the default fields
		  $this->default_init($hand,$_POST);
		  $this->extra_init($hand,$_POST);
		// The new handling method is saved
      	$id=$hand->save();
		//Then the handling is loaded back so you can add the extras!
		$hand=Handling::load($id);
		$hand_a=(array)$hand;
		$this->handling_form($hand_a,$err,payment_update_title);
	 	}
  	  }elseif($_GET['action']=='add'){
    	$this->handling_form($row,$err,handling_add_title);
  	  }else{
		$this->handling_list();
	  }
  }

function _load_extra($e_class){
	$e_file="admin/$e_class.php";
  if($this->dyn_load($e_file)){
		$e=new $e_class;
		return $e;
	}
}

function extra_fill(&$hand, &$data){
	if($pm=$this->_load_extra('pm_'.$data['handling_payment'].'_View')){
    return $pm->pm_fill($hand,$data);
	}
}

// Loads default extras for payment method eg."pm_paypal_View.php"
function extra_init(&$hand, &$data){
	if($pm=$this->_load_extra('pm_'.$data['handling_payment'].'_View')){
    return $pm->pm_init($hand,$data);
	}
}

function extra_check(&$data, &$err){
	if($pm=$this->_load_extra('pm_'.$data['handling_payment'].'_View')){
    return $pm->pm_check($data,$err);
	}
}

function extra_view(&$data){
	if($pm=$this->_load_extra('pm_'.$data['handling_payment'].'_View')){
    return $pm->pm_view($data);
	}
}

function extra_form(&$data,&$err){

	if($pm=$this->_load_extra('pm_'.$data['handling_payment'].'_View')){
    return $pm->pm_form($data,$err);
	}
}


function default_init(&$hand, &$data){

	switch ($data['handling_payment']) {
    case "invoice" : $hand->handling_text_payment=
		  "{fr}Virement{/fr}{de}&Uuml;berweisung{/de}{it}Giraconto{/it}{en}Invoice{/en}";
      break;
    case "entrance" : $hand->handling_text_payment=
      "{fr}A l'entr&eacute;e{/fr}{de}Zum Eintritt{/de}{it}A l'entrata{/it}{en}At the entrance{/en}";
      break;
    case "cash" : $hand->handling_text_payment=
      "Cash";
      break;
	}


	switch ($data['handling_shipment']) {
    case "email" : $hand->handling_text_shipment=
		  "{fr}Par &eacute;mail{/fr}{de}Bei E-Mail{/de}{it}Per Email{/it}{en}By e-mail{/en}";
      break;
    case "post" : $hand->handling_text_shipment=
      "{fr}Par la poste{/fr}{de}Mit der Post{/de}{it}Per posta{/it}{en}By post{/en}";
      break;
    case "entrance" : $hand->handling_text_shipment=
      "{fr}A l'entr&eacute;e{/fr}{de}Zum Eintritt{/de}{it}A l'entrata{/it}{en}At the entrance{/en} ";
      break;
    case "sp" : $hand->handling_text_shipment=
      "{fr}Point de vente{/fr}{de}Verkafstelle{/de}{it}Punto di venduta{/it}{en}Salepoint{/en} ";
      break;
	}
}
}
?>