<?php 

class KintoneForm_file
{

	/*
	 * get instance
	 */
	public static function getInstance()
	{
		/**
		* a variable that keeps the sole instance.
		*/
		static $instance;

		if ( !isset( $instance ) ) {
			$instance = new KintoneForm_file();
		}
		return $instance;
	}

	public static function get_kintone_file_key( $kintone_setting_data, $appdata, $cf7_send_data, $kintone_form_data, $cf7_mail_tag, $e ) {

		$return_data = array();

		$value = $cf7_send_data[$cf7_mail_tag];

		if( $kintone_form_data['required'] == 'true' && empty($value) ){
			$e->add('Error', $cf7_mail_tag .'->'. $kintone_form_data['code'].' : Required fields');
		}		

		$submission = WPCF7_Submission::get_instance();
		if ( empty( $submission ) ) {
		    return;
		}		
		$uploaded_files = $submission->uploaded_files();

		if(!isset($uploaded_files[$cf7_mail_tag])){
			return;
		}

		$filePath = $uploaded_files[$cf7_mail_tag];
		$fileName = mb_convert_encoding(mb_substr($filePath, mb_strrpos($filePath, DIRECTORY_SEPARATOR) + 1), "UTF-8", "auto");
		$fileSize = filesize( $filePath );

		$finfo = finfo_open(FILEINFO_MIME_TYPE);  
		$mimeType = finfo_file($finfo, $filePath);
		$file_data = file_get_contents($filePath);
		finfo_close($finfo);  
		
		$request_url = 'https://'.$kintone_setting_data['domain'].'/k/v1/file.json';
		$headers = array( 
			'X-Cybozu-API-Token' 	=> $appdata['token'] ,
		);

		$boundary = '----'.microtime(true);
		$body = '--'.$boundary."\r\n".
		        'Content-Disposition: form-data; name="file"; filename="'.$fileName.'"'."\r\n".
		        'Content-Type: '.$mimeType."\r\n\r\n".
		        $file_data."\r\n".
		        '--'.$boundary.'--';

		$res = wp_remote_post( 
			$request_url, 
			array(
		    	'headers' => array(
		    		'Content-Type' => "multipart/form-data; boundary={$boundary}", 
		    		'X-Cybozu-API-Token' 	=> $appdata['token'],
		    		'Content-Length' 	=> strlen($body),
				),
		    	'body' => $body
			)
		);

		if ( is_wp_error( $res ) ) {

			$e = $res;
			return $res;

		} else {

			$return_value = json_decode( $res['body'], true );
			$return_data['value'][] = $return_value;
			
			if ( isset( $return_value['message'] ) && isset( $return_value['code'] ) ) {

				$e->add('Error', $cf7_mail_tag .'->'. $kintone_form_data['code'].' : '.$return_value['message'] . '(' . $return_value['code'] . ')' );

				return;
			}

			return $return_data;
		}

	}

}