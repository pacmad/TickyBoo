<?php
/*********************** %%%copyright%%% *****************************************
 *
 * FusionTicket - ticket reservation system
 * Copyright (C) 2007-2008 Christopher Jenkins. All rights reserved.
 *
 * Original Design:
 *  phpMyTicket - ticket reservation system
 *   Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
 *
 * This file is part of fusionTicket.
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
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * Contact info@noctem.co.uk if any conditions of this licencing isn't
 * clear to you.
 */

 
class Model {
  /**
  * The name of this model
  * @var string 
  */
  protected $_idName;
  protected $_tableName;
  protected $_columns = array();

  protected $_errors = array();

  
  function __construct()
  {
    if (!$this->_columns) {
      $defs = & ShopDB::FieldListExt($this->tableName);
      foreach($defs as $key => $value) {
        If ($key != $this->_idName) {
          $this->$key = $value->Default;
          if ($value->Null == 'YES') $key = '*'.$key;
          $this->_columns[]  = $key;
        }
      }
    }  
  }

  function save ($id = null){
    if(isset($id) and !$id) $this->id = $id;
    if($this->id){
      return $this->update();
    }else{
     return  $this->insert();
    }
  }

  function insert()
  { 
  // $this->_idName 
  // unset($this->$this->_idName);
    $values  = join(",", $this->quoteColumnVals());

    if (ShopDB::query("INSERT INTO `{$this->_tableName}` SET $values ")) {
      $this->{$this->_idName} = ShopDB::insert_id();
      return $this->id;
    } else
      return false;
  }

  function update()
  {

    $values  = join(",", $this->quoteColumnVals());

    $id  = _esc($this->id);
    $sql = "UPDATE `{$this->_tableName}` SET $values WHERE `{$this->_idName}` = $id "  ;
    if (ShopDB::query($sql)) {
      return $this->id; // Not always correct due to mysql update bug/feature
    } else
      return false;
    
  }

  function quoteColumnVals() {
    $vals = array(); 
    foreach($this->_columns as $key) {
      if ($val= $this->_set($key)) {
        $vals[] = $val;
      }
    }
    return $vals;
   }

  function _set ($key, $value='~~~',$mandatory=FALSE){
    $type= substr($key,0,1);
    if ($type == '#') {
      $key = substr($key,1);
      $mandatory = true;
      if ($value === 0)
        $value = null;
    } elseif ($type == '*') { 
      $key = substr($key,1);
      $mandatory = true;
    }
    if($value =='~~~'){
      $value= $this->$key;
    }
    
    if($value or $mandatory){
      return "`{$key}`="._esc($value);
    }
  }
  
  function delete($id)  {
    if (!$id) $id = $this->id;
    $id = _esc($id);
    ShopDB::query("DELETE FROM `{$this->_tableName}` WHERE `{$this->_idName}` = $id ");
    return ShopDB::affected_rows();
  }

  Function CheckValues ($arr) { 
    foreach($this->_columns as $key){
      $type= substr($key,0,1);
      if ($type == '#') {
        $key = substr($key,1);
      } elseif ($type == '*') { 
        $key = substr($key,1);
        if(empty($arr[$key])){$this->_errors[$key]=con('mandatory');}
      }
    }
    return (count($this->_errors)==0);
  }

  function fillPost($nocheck=false)    { return $this->_fill($_POST,$nocheck); }
  function fillGet($nocheck=false)     { return $this->_fill($_GET ,$nocheck); }
  function fillRequest($nocheck=false) { return $this->_fill($_REQUEST ,$nocheck); }

  function _fill($arr , $nocheck=true)  {
    if(is_array($arr) and ($nocheck or $this->CheckValues ($arr)))
    { 
      foreach($arr as $key => $val)
        $this->$key = $val;
      return true;
    }
    return false;
   }

  function errors() {
    return $this->_errors;
  }

   function SetError($mess, $key='_system') {
  //      ShopDB::Rollback();
    $this->_errors[$key] .= con($mess);
    return false;
  }

   function values () {
    $result = parent::values();
    $result[$this->_idName] = $this->id;
    return $result;
  }

  function _abort ($str=''){
    if ($str) {
      echo "<div class=error>{$str}</div>";
    }
    ShopDB::rollback($str);
    return false; // exit;
  }

  /**
   * When a something is requested instead of talking directly to the var in the class
   * it is called via the __get method.
   * 
   * @param $key : the parameters name.
   * Last Updated : 15/11/2008 01:30 CJ
   */   
  function __get($key) {    
    if ($key==='id') {
      $_idName = $this->_idName;
      return $this->$_idName;
    }/* elseif(substr($key, 0, 2) == '__') {
      return htmlspecialchars($this->data[substr($key, 2)]);
    } elseif (array_key_exists($key, $this->extra)) {
      return $this->extra[$key];
    }else{
      return parent::__get($key);
    }*/
  }
}

?>