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
require_once("classes/AUIComponent.php");

class MenuAdmin extends AUIComponent {
  function __construct(){
    $menu_items = array (
    	"index.php" 	    	=> "index_admin|control",
    	"view_users.php"	  => "tabs.admins_admin|admin",
    	"view_event.php"	  => "tabs.events_admin|admin",
    	"view_banners.php"  => "banner_admin|admin",
    	"view_stats.php"	  => "statistic_admin|organizer",
    	"view_order.php"	  => "orders_admin|admin",
    	"view_template.php"	=> "template_admin|admin",
    	"view_handling.php"	=> "handlings_admin|admin",
    	"view_search.php"	  => "search_admin|admin",
    	"view_impexp.php"	  => "transports_admin|admin",
    	"view_utils.php"	  => "utilities_admin|admin"
    );

    $menu_items = plugin::call('_adminMenuItems',$menu_items);
   
    foreach($menu_items as $link => $text){
      list($txt,$role) = explode('|',$text );
      plugin::call('AddACLResource',$txt, $role );
      $this->menu_items[$link] = $txt;
    }
  }
  function draw () {
    global $_SHOP;

		// Specify menu items in an array
		//  "file name" 		=> "text_define"    (text define from /includes/lang/site_XX.inc)

 //   plugin::call('ACLShow');
		// Begin drawing the menu table
		echo "<center>
        <table width='{$this->width}' class='menu_admin' cellspacing='1' >
        <tr><td  class='menu_admin_title'>" . con('administration') . "</td></tr>";

		// Loop through the menu item array and put the'menu_admin_link_selected'-class on the linkt to current file
		foreach($this->menu_items as $link => $text){
     // if ($_SHOP->admin->isAllowed($text)) {
			  echo "<tr><td  class='menu_admin_item'><a href={$link} ";
  			if ($text=="{$this->current_page}_admin"){
  				echo "class='menu_admin_link_selected'>";
  			} else {
  				echo "class='menu_admin_link'>";
  			}
  			echo con($text);
  			echo "</a></td></tr>";
  		}
	//	}
    echo "<tr><td></td></tr>";
    echo "<tr><td  class='menu_admin_item'>
     <a href='{$_SERVER["PHP_SELF"]}?action=logout' class='menu_admin_link'>" . con('logout') . "</a></td></tr>
     </table>
    </center><br>";
  }
}

?>