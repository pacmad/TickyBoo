<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2010
 */
class plugin_custom_used extends baseplugin {

	public $plugin_info		  = 'Custom plugin';
	/**
	 * description - A full description of your plugin.
	 */
	public $plugin_description	= 'This plugin can be used to handle events';
	/**
	 * version - Your plugin's version string. Required value.
	 */
	public $plugin_myversion		= '0.8.1';
	/**
	 * requires - An array of key/value pairs of basename/version plugin dependencies.
	 * Prefixing a version with '<' will allow your plugin to specify a maximum version (non-inclusive) for a dependency.
	 */
	public $plugin_requires	= null;
	/**
	 * author - Your name, or an array of names.
	 */
	public $plugin_author		= 'The FusionTicket team';
	/**
	 * contact - An email address where you can be contacted.
	 */
	public $plugin_email		= 'info@fusionticket.com';
	/**
	 * url - A web address for your plugin.
	 */
	public $plugin_url			= 'http://www.fusionticket.org';

  public $plugin_actions  = array ('config','install','uninstall','priority','enable','protect');

  function doOrderDiscription($order, $discription) {
    return 'test';
  }

  function doOrderencodebarcode($order, $ticket, $code) {
    return str_pad(dechex($code), 10, "0", STR_PAD_LEFT);
  }

  function doOrderdecodebarcode($barcode) {
    return (sscanf(str_pad(hexdec($codebar), 16, "0", STR_PAD_LEFT),"%08d%s"));
  }

}

?>