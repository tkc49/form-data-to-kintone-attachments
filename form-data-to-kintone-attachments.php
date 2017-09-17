<?php
/**
 * Plugin Name: Form data to kintone Attachments.
 * Plugin URI:  
 * Description: This plugin is an addon for "kintone form".
 * Version:	 1.1.3
 * Author:	  Takashi Hosoya
 * Author URI:  http://ht79.info/
 * License:	 GPLv2 
 * Text Domain: kintone-form-attachments
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2017 Takashi Hosoya ( http://ht79.info/ )
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

define( 'KINTONE_FORM_ATTACHMENTS_URL',  plugins_url( '', __FILE__ ) );
define( 'KINTONE_FORM_ATTACHMENTS_PATH', dirname( __FILE__ ) );


$KintoneFormAttachments = new KintoneFormAttachments();
$KintoneFormAttachments->register();

require KINTONE_FORM_ATTACHMENTS_PATH . '/modules/file.php';
require_once( KINTONE_FORM_ATTACHMENTS_PATH . '/inc/BFIGitHubPluginUploader.php' );



class KintoneFormAttachments {

	private $version = '';
	private $langs   = '';
	private $nonce   = 'kintone_form_attachments_';
		
	function __construct()
	{
		$data = get_file_data(
			__FILE__,
			array( 'ver' => 'Version', 'langs' => 'Domain Path' )
		);
		$this->version = $data['ver'];
		$this->langs   = $data['langs'];
		
	}

	public function register()
	{
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 1 );
	}

	public function plugins_loaded()
	{
		load_plugin_textdomain(
			'kintone-form-attachments',
			false,
			dirname( plugin_basename( __FILE__ ) ).$this->langs
		);

		if ( is_admin() ) {
		    new BFIGitHubPluginUpdater( __FILE__, 'tkc49', "form-data-to-kintone-attachments" );
		}		

		add_filter( 'kintone_form_attachments_data', array( $this, 'kintone_form_attachments_data' ), 10, 6 );
		add_filter( 'kintone_fieldcode_supported_list', array( $this, 'kintone_fieldcode_supported_list' ), 10, 1 );

		
	}

	public function kintone_form_attachments_data( $kintone_setting_data, $appdata, $cf7_send_data, $kintone_form_data, $cf7_mail_tag, $e ){

		return KintoneForm_file::get_kintone_file_key( $kintone_setting_data, $appdata, $cf7_send_data, $kintone_form_data, $cf7_mail_tag, $e );

	}

	public function kintone_fieldcode_supported_list( $kintone_fieldcode_supported_list ){

		$kintone_fieldcode_supported_list['FILE'] = 'file';

		return $kintone_fieldcode_supported_list;

	}
}
