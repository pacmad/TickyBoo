<?php
/**
%%%copyright%%%
 *
 * FusionTicket - ticket reservation system
 *  Copyright (C) 2007-2010 Christopher Jenkins, Niels, Lou. All rights reserved.
 *
 * Original Design:
 *	phpMyTicket - ticket reservation system
 * 	Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
 *
 * This file is part of FusionTicket.
 *
 * This file may be distributed and/or modified under the terms of the
 * "GNU General Public License" version 3 as published by the Free
 * Software Foundation and appearing in the file LICENSE included in
 * the packaging of this file.
 *
 * This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
 * THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE.
 *
 * Any links or references to Fusion Ticket must be left in under our licensing agreement.
 *
 * By USING this file you are agreeing to the above terms of use. REMOVING this licence does NOT
 * remove your obligation to the terms of use.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * Contact help@fusionticket.com if any conditions of this licencing isn't
 * clear to you.
 */
if (!defined('ft_check')) {die('System intrusion ');}

//require_once("admin/class.adminview.php");
If (!defined('ACQUIRERTIMEOUT')) {
  define ('ACQUIRERTIMEOUT',10);
  define ('TRACELEVEL', 'DEBUG,ERROR');
  define ('EXPIRATIONPERIOD','PT10M');
}

require_once('classes/class.payment.php');
require_once('ideal-mollie/ideal.class.php');

class eph_mollie extends payment{

  public $extras = array('pm_mollie_partnerid','pm_mollie_test');
  public $mandatory = array('pm_mollie_partnerid' ); // is only used in project vazant.

  function admin_form (){
    if (!in_array('ssl', stream_get_transports()))
    {
    	addWarning('Uw PHP installatie heeft geen SSL ondersteuning. SSL is nodig voor de communicatie met de Mollie iDEAL API.');
    }

    return "{gui->input name='pm_mollie_partnerid'}".
           "{gui->checkbox name='pm_mollie_test'}";
	}

	function admin_init (){
  	$this->handling_text_payment    = "iDEAL via Mollie";
		$this->handling_text_payment_alt= "iDEAL via Mollie";
//    $this->handling_html_template  .= "";
		$this->pm_mollie_test  = true;
	}

	function on_confirm(&$order) {
    global $_SHOP;
    $ideal = new iDEAL_Payment ($this->pm_mollie_partnerid);
    $ideal->setTestmode($this->pm_mollie_test);
    $Issuers =& $ideal->getBanks();
    if ($Issuers == false) {
      return array('approved'=>false,
                   'transaction_id'=>$ideal->getErrorCode().' - '.$ideal->getErrorMessage(),
                   'response'=> $ideal->getConsumerInfo());
    }

    $_SHOP->smarty->assign('ideal_issuers', $Issuers );

    //print_r($order);

    return "
      {gui->StartForm  width='100%' id='payment-confirm-form' action='{$_SHOP->root_secured}checkout.php' method='POST' onsubmit='this.submit.disabled=true;return true;'}
        {gui->hidden name='action' value='submit'}
        {gui->hidden name='sor' value='".Order::EncodeSecureCode($order,'')."'}
        {gui->selection name='bank_id' options=\$ideal_issuers}
      {gui->EndForm title=!pay! align='right' noreset=true}";
	}

  function on_submit($order) {
    global $_SHOP;
    $ideal = new iDEAL_Payment ($this->pm_mollie_partnerid);
    $ideal->setTestmode($this->pm_mollie_test);

    $return_url = $_SHOP->root_secured. 'checkout_accept.php?'.$order->EncodeSecureCode(null);
    $report_url = $_SHOP->root_secured. 'checkout_notify.php?'.$order->EncodeSecureCode(null);
    if (isset($_POST['bank_id']) and !empty($_POST['bank_id'])) {
     	if ($ideal->createPayment((int) $_POST['bank_id'], (int)($order->order_total_price *100), $order->order_description(), $return_url, $report_url)) 	{
    		/* Hier kunt u de aangemaakte betaling opslaan in uw database, bijv. met het unieke transactie_id
    		   Het transactie_id kunt u aanvragen door $iDEAL->getTransactionId() te gebruiken. Hierna wordt
    		   de consument automatisch doorgestuurd naar de gekozen bank. */
     		$transactionID = $ideal->getTransactionId();
        $order->order_payment_id=$transactionID;
        Order::set_payment_id($order->order_id,'mollie:'.$transactionID) ;
        $order->set_payment_status('pending');

     		header("Location: " . $ideal->getBankURL());
    //    echo "<script type=\"text/javascript\" language=\"JavaScript\">\nwindow.location='".trim($ideal->getBankURL())."';\n</script>";
        return '';
    	} else {
        return array('approved'=>false,
                     'transaction_id'=>$ideal->getErrorCode().' - '.$ideal->getErrorMessage(),
                     'response'=> implode('<br>',$ideal->getConsumerInfo()));

    	}
    }
  }

  function on_return(&$order, $result){
    global $_SHOP;
    if (isset($_GET['transaction_id'])) {
      return array('approved'=>true,
                   'transaction_id'=>($_GET['transaction_id']) ,
                   'response'=> con('eph_ideal_waitingaception'));
    } else {
      return array('approved'=>false,
                   'transaction_id'=>null ,
                   'response'=> 'Reason: '.print_r($_REQUEST,true));
    }
  }

  function on_check(&$order){
    global $_SHOP;
    if (isset($_GET['transaction_id']))  {
      $ideal = new iDEAL_Payment ($this->pm_mollie_partneri);
      $ideal->setTestmode($this->pm_mollie_test);
    	$ideal->checkPayment($_GET['transaction_id']);
/*
      $debug = print_r($request, true). "\n";
      $debug .= print_r($ideal, true). "\n";
    $handle=fopen($_SHOP->tmp_dir."mollie.log","a");
    fwrite($handle,$debug);
    fclose($handle);
*/
	    if ($ideal->getPaidStatus() == true){
  	    $order->order_payment_id=$_GET['transaction_id'];
  	    Order::set_payment_id($order->order_id,'ideal:'.$_GET['transaction_id']);
        $order->set_payment_status('payed');
        return true;
      } else {
        OrderStatus::statusChange($order->order_id,'mollie-notify',NULL,'notify::notpaide',print_r(	$ideal->consumer_info,true));
        $order->delete($order->order_id, con('mollie_notpaid_notify'));
        return true;
      }
    }
  }
}
?>