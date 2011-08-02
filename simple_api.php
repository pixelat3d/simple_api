<?php
/* 
	simple_api.php 
	Requires php cURL (http://www.php.net/manual/en/book.curl.php)
	Created: 02.14.10
	Last Updated: 08.02.2011
*/

class simple_api { 
	public $format = 'json'; 
	public $method = 'get';
	public $timeout = 3000;

	protected $username;
	protected $password;
	protected $useragent;
	protected $post_string;
	protected $last_url;
	protected $current_url;
	protected $last_response; 
	protected $response; 
	protected $headers = Array( );
	protected $data;
	protected $error;

	private	$version = 0.72;

	public function __construct( $username = '', $password = '' ) {
		$this->username = $username;
		$this->password = $password;

		$this->useragent = 'Simple API/'.$this->version;
	}

	protected function extract_postvars( $url ) {
		// This isn't used unless you enable the 
		// get -> post checking below.
		$url = substr( $url, strpos( $url, '?' ) + 1 );
		$postvars = explode( '&', $url );

		return( $postvars );
	}

	public function add_headers( $headers ) { 
		$this->headers = $headers; 
	}

	private function clear_headers( ) { 
		$this->headers = Null;
	}

	final public function request_data( $url, $format = 'json', $method = 'get', $data = '', $decode = true ) {
		$ch	= curl_init( $url )or die( 'Could not Init CURL!' );
		$this->last_url = $this->current_url; 
		$this->current_url = $url;
		$this->data = $data;

		$method = strtolower( $method ); 
		if( $method == strtolower( 'post' ) || $method == strtolower( 'get' ) ) {
			$this->method = $method;
		}

		if( $format == strtolower( 'json' ) || $format == strtolower( 'xml' ) ) {
			$this->format = $format;
		}
		
		// If requred auth, then auth. 	
		if ( !empty( $this->username ) && !empty( $this->password ) ) {
			curl_setopt ( $ch, CURLOPT_USERPWD, $this->username.':'.$this->password);
		}

		//Check for HTTP Headers. 
		if( !empty( $this->headers ) ) {
			//print_r( $this->headers );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $this->headers );
		}

		/* Not going to store cookies on the server.
		$ckfile = tempnam ("/tmp/", "CURLCOOKIE");
		curl_setopt( $ch, CURLOPT_COOKIEJAR, $ckfile ); */

		curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
		curl_setopt( $ch, CURLOPT_NOBODY, 0 );
		curl_setopt( $ch, CURLOPT_HEADER, false ); 
		curl_setopt( $ch, CURLOPT_TIMEOUT_MS, $this->timeout  );
		curl_setopt( $ch, CURLOPT_USERAGENT, $this->useragent );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		if( $this->method == 'post' ) {
			if( is_array( $data ) ) {
				$post_str = '';
				foreach( $data as $key => $val ) {
					$key = urlencode( $key );
					$val = urlencode( $val );
					$post_str .= $key.'='.$val.'&';
				}

				$this->post_string = $post_str;
				$this->post_string = rtrim( $this->post_string, '&' );
				$this->last_query = $url;
				
				curl_setopt( $ch, CURLOPT_URL, $url );
				curl_setopt( $ch, CURLOPT_POST, count( $data ) );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $this->post_string );
			} else { 
				// Can't post XML like this, it will break. 
				// So if you want to POST XML to a URL you'll have to just
				// if you want to catch get vars that are acccidentally put
				// in with a post call, uncomment the code below.  

				/*
				//If they didn't do it right, try and revocer anyway.
				$fields 	= $this->extract_postvars( $url ); 
				//$url1 	= substr( $url, 0, strpos( $url, '?'. 0 ) -1 );
				$url1 = $url;
				$url2		= substr( $url, strpos( $url, '?', 0 ) + 1 );

				curl_setopt( $ch, CURLOPT_URL, $url );
				curl_setopt( $ch, CURLOPT_POST, count( $fields ) );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $url2 );

				$this->post_string = 'Empty.';
				$this->last_query = $url1;*/

				curl_setopt( $ch, CURLOPT_POST, 1 ); 
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
			}
		}

		try {
			$response = curl_exec( $ch );
			if( $response === false ) {
				$this->error = curl_error( $ch );	
				throw new Exception( $this->error );
			} else { 

				$r_info		= curl_getinfo( $ch );
				$this->last_response 	= $this->response; 	
				$this->response		= $response;
					
				if( $r_info['http_code'] == 200 ) {
				switch( strtolower( $this->format ) ) {
					case 'json':
						if( !$decode ) { 
							return( $response );
						} else { 
							return ( json_decode( $response ) ); 
						}
					break; 
				
					case 'xml': 
						return( $response ); 
					break;
				
					default: 
						return( $response );
					break;
				}
					return( $r_info['http_code'] );
				} else {
					$this->last_response = $this->response;
					$this->response = "SimpleAPI/HTTP Error # $r_info[http_code] <br /> $response -- ".curl_error( $ch ); 
				}
			}
		} catch( Exception $e ) {

		}
		curl_close( $ch );
	}

	public function debug_me( ) {
		print_r( $this );
	}

	public function get_user( ) { return( $this->username ); }
	public function set_user( $username ) { $this->username = $username; }

	public function set_password( $password ) { $this->password = $password; }
	public function get_password( ) { return( $this->password ); }

	public function set_useragent( $agent ) { $this->useragent = $agent; }
	public function get_useragent( ) { return( $this->useragent ); }
	public function last_query( ) { return( $this->last_query ); }
	public function last_response( ) { return( $this->last_response ); }
}

?>
