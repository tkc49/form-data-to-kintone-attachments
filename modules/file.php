<?php

class KintoneForm_file {

	/*
	 * get instance
	 */
	public static function getInstance() {
		/**
		 * a variable that keeps the sole instance.
		 */
		static $instance;

		if ( ! isset( $instance ) ) {
			$instance = new KintoneForm_file();
		}

		return $instance;
	}

	public static function get_kintone_file_key( $kintone_setting_data, $appdata, $cf7_send_data, $kintone_form_data, $cf7_mail_tag, $e ) {

		$return_data = array();

		$value = $cf7_send_data[ $cf7_mail_tag ];

		if ( 'true' === $kintone_form_data['required'] && empty( $value ) ) {
			$e->add( 'Error', $cf7_mail_tag . '->' . $kintone_form_data['code'] . ' : Required fields' );
		}

		$submission = WPCF7_Submission::get_instance();
		if ( empty( $submission ) ) {
			return;
		}
		$uploaded_files = $submission->uploaded_files();

		if ( ! isset( $uploaded_files[ $cf7_mail_tag ] ) ) {
			return;
		}

		$file_path = $uploaded_files[ $cf7_mail_tag ];
		$file_name = mb_convert_encoding( mb_substr( $file_path[0], mb_strrpos( $file_path[0], DIRECTORY_SEPARATOR ) + 1 ), 'UTF-8', 'auto' );

		$finfo     = finfo_open( FILEINFO_MIME_TYPE );
		$mime_type = finfo_file( $finfo, $file_path[0] );
		$file_data = file_get_contents( $file_path[0] );
		finfo_close( $finfo );

		$request_url = Kintone_Form_Utility::get_kintone_url( $kintone_setting_data, 'file' );

		$boundary = '----' . microtime( true );
		$body     = '--' . $boundary . "\r\n" . 'Content-Disposition: form-data; name="file"; filename="' . $file_name . '"' . "\r\n" . 'Content-Type: ' . $mime_type . "\r\n\r\n" . $file_data . "\r\n" . '--' . $boundary . '--';

		$res = wp_remote_post(
			$request_url,
			array(
				'headers' => array(
					'Content-Type'       => "multipart/form-data; boundary={$boundary}",
					'X-Cybozu-API-Token' => $appdata['token'],
					'Content-Length'     => strlen( $body ),
				),
				'body'    => $body,
			)
		);

		if ( is_wp_error( $res ) ) {
			return $res;
		} else {
			$return_value           = json_decode( $res['body'], true );
			$return_data['value'][] = $return_value;

			if ( isset( $return_value['message'] ) && isset( $return_value['code'] ) ) {

				$e->add( 'Error', $cf7_mail_tag . '->' . $kintone_form_data['code'] . ' : ' . $return_value['message'] . '(' . $return_value['code'] . ')' );

				return;
			}

			return $return_data;
		}

	}

}
