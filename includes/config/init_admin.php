<?php
/*
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

 */

global $_SHOP;
require_once("init_common.php");


$_SHOP->session_name = "AdminSession";
//$_SHOP->is_admin = true;
$_SHOP->auth_required = true;
$_SHOP->auth_table = "Admin";
$_SHOP->auth_login = "admin_login";
$_SHOP->auth_password = "admin_password";

$_SHOP->allowed_uploads = array('jpg', 'jpeg', 'png', 'gif', 'mp3');

require_once("functions/init.php");

if ($_SHOP->organizer_id != $_SESSION['_SHOP_AUTH_USER_DATA']['admin_id']) {
    session_destroy();
    exit;
}

?>