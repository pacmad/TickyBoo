{*
%%%copyright%%%
 * phpMyTicket - ticket reservation system
 * Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
 *
 * This file is part of phpMyTicket.
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
 * The "phpmyticket professional licence" version 1 is available at
 * http://www.phpmyticket.com/ and in the file
 * PROFESSIONAL_LICENCE included in the packaging of this file.
 * For pricing of this licence please contact us via e-mail to 
 * info@phpmyticket.com.
 * Further contact information is available at http://www.phpmyticket.com/
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * Contact info@phpmyticket.com if any conditions of this licencing isn't 
 * clear to you.
 
 *}<table width="150" border="0" cellspacing="0" cellpadding="0" >
{event_group group_status='pub'}
   <tr> 
     <td height="24" style="padding-left:10px;padding-bottom:10px; ">
     <a class='shop_link' href='shop.tpl?event_group_id={$shop_event_group.event_group_id}'>
     {$shop_event_group.event_group_name}<img src="images/link.png" valign='bottom' border='0'></a></td>
   </tr>
{/event_group}
 </table>
			
<table width="150" border="0" cellspacing="0" cellpadding="0" class='menu'>
<tr> <td height="24" class="menu_td">
     <a class='shop_link' href='calendar.tpl'>
    {fr}Calendrier{/fr}
    {de}Kalender{/de}
    {it}Calendario{/it}
    {en}Calendar{/en}
     <img src="images/link.png" border='0' valign='bottom'></a>
</td></tr>
<tr> <td height="24" class="menu_td">
     <a class='shop_link' href='event_groups.tpl'>
    {fr}Festivals{/fr}
    {de}Festivals{/de}
    {it}Festival{/it}
    {en}Festivals{/en}
     <img src="images/link.png" border='0' valign='bottom'></a>
</td></tr>
</table>
<table width="150" border="0" cellspacing="0" cellpadding="0" class='menu'>

<tr> <td height="24" class="menu_td">
     <a class='shop_link' href='conditions.tpl'>
    {fr}Commander?{/fr}
    {de}Wie Bestellen?{/de}
    {it}Ordinare?{/it}
    {en}How to?{/en}
     
     
     <img src="images/link.png" border='0' valign='bottom'></a>
</td></tr>
<tr> <td height="24" class="menu_td">
     <a class='shop_link' href='about.tpl'>    
    {fr}A propos{/fr}
    {de}&Uuml;ber uns{/de}
    {it}Profilo{/it}
    {en}About{/en}
<img src="images/link.png" border='0' valign='bottom'></a>
</td></tr>

<tr> <td height="24" class="menu_td">
     <a class='shop_link' href='contact.tpl'>    
    {fr}Contact{/fr}
    {de}Kontakt{/de}
    {it}Contatto{/it}
    {en}Contact{/en}
<img src="images/link.png" border='0' valign='bottom'></a>
</td></tr>
</table>

{* *****User****** *}

{if $smarty.get.action eq 'login'}
  {user->login username=$smarty.post.username password=$smarty.post.password}
{elseif $smarty.get.action eq 'logout'}
 {user->logout}
{/if}

{if $user->logged}
<table width="150" border="0" cellspacing="0" cellpadding="0" class='menu'>
<tr> <td height="24" class="menu_login">
  {fr}Bienvenue{/fr}
  {de}Willkommen{/de}
  {it}Benvenuto{/it}
  {en}Welcome{/en}
  {user->user_firstname} {user->user_lastname}!
  <a href='shop.tpl?action=logout'>{en}logout{/en}{de}logout{/de}{it}logout{/it}{fr}<font size='-2'>Tcho, je me casse...</font>{/fr}</a>
</td></tr></table>
{else}
<form method=post action=shop.tpl?action=login>
<table width='150' border="0" cellspacing="0" cellpadding="0"  class='menu'>
   <tr><td class="menu_login">    
    {fr}Email{/fr}
    {de}E-Mail{/de}
    {it}Email{/it}
    {en}Email{/en}
</td><td align='left'><input type=input name=username size=8></td></tr> 
   <tr><td  class="menu_login">    
    {fr}Mot de passe{/fr}
    {de}Passwort{/de}
    {it}Parola d'accesso{/it}
    {en}Password{/en}
</td><td align='left'><input type=password name=password size=8></td></tr>
   <tr><td colspan=2 align='center'><input type=submit value='{fr}entrer{/fr}{de}login{/de}{it}entrare{/it}{en}login{/en}'></td></tr>
</table>
</form>
{/if} 


{* *****Panier****** *}

<table width="100%" border="0" cellspacing="3" cellpadding="0" style='border-top:#45436d 1px solid; padding-top:5px;padding-bottom:5px;'>
  <tr> 
    <td class='cart_menu_title' align='left' style='padding-left:10px;'>
    {if $cart->is_empty_f() }
      <img src="images/caddie1.png">
    {else} 
      <a href='shop.tpl?action=view_cart' class='shop_link'>
      <img src="images/caddie_full1.png" border='0'>
    {/if}
    {fr}Panier d'achats{/fr}
    {de}Einkaufskorb{/de}
    {en}Shopping cart{/en}
    {it}Carrello{/it}</a>
    </td>
  </tr>
  <tr> 
    
   {if $cart->is_empty_f() }
       <td valign="top" class='cart_menu'>
        {fr}Panier vide{/fr}
	{de}Leerer Korb{/de}
	{en}Cart empty{/en}
	{it}Carrello vuoto{/it}
       </td>
      
    {else}
      {assign var="cart_overview" value=$cart->overview_f() }
       
       
       <td valign="top" class='cart_menu'>
    {if $cart_overview.valid }  
       <img src='images/ticket-valid.png'> {$cart_overview.valid}
     {/if}
     {if $cart_overview.expired}
     <img src='images/ticket-expired.png'> {$cart_overview.expired}
     {/if}  
    {if $cart_overview.valid }  
       <img src='images/clock.gif'> {$cart_overview.minttl}'
     {/if}
      
       </td>
    {/if}
  </tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="5" class='menu_langs'>
  <tr> 
    <td><div align="center">
        <a href='shop.tpl?setlang=de' class='langs_link'>[de]</a> 
	<a href='shop.tpl?setlang=fr' class='langs_link'>[fr]</a>
	<a href='shop.tpl?setlang=en' class='langs_link'>[en]</a>
	<a href='shop.tpl?setlang=it' class='langs_link'>[it]</a></div></td>
  </tr>
</table>
<br><br>
