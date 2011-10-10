<?php
/* 
	simple_api.php 
	Requires php cURL (http://www.php.net/manual/en/book.curl.php)
	php version: 5.1.2
	Created: 02.14.10
	Last Updated: 08.15.2011
*/

class simple_api { 
	protected $method = 'get';
	protected $format = 'json';
	protected $timeout = 3000;
	protected $decode = true;

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
	protected $retries;
	protected $max_retries;

	private	$version = 0.74;

	public function __construct( $username = '', $password = '' ) {
		$this->username = $username;
		$this->password = $password;
		$this->max_retries = 3; 
		$this->retries = 0;

		$this->useragent = 'Simple API/'.$this->version;
	}

	public function add_headers( $headers ) { 
		$this->headers = $headers; 
	}

	public function add_header( $header ) { 
		$this->headers[] = $header; 
	}

	private function clear_headers( ) { 
		$this->headers = Null;
	}

	final public function request_data( $url, $format = 'json', $method = 'get', $data = '', $decode = true ) {
		$this->last_url = $this->current_url; 
		$this->current_url = $url;
		$this->data = $data;

		$method = strtolower( $method ); 
		if( $method == 'post' || $method == 'get' ) {
			$this->method = $method;
		} else { 
			$method = $this->method;
		}

		$format = strtolower( $format );
		if( $format ==  'json' || $format == 'xml' ) {
			$this->format = $format;
		} else { 
			$format = $this->format;
		}

		if( $method == 'get' ) { 
			//Check $data for GET variables in case people decide to just 
			//use $url as an entrypoint.
			if( !empty( $data ) ) { 
				$url = rtrim( $url, '?' );

				if( is_array( $data ) ) {
					$querystring = http_build_query( $data, '', '&' );
				} else {
					//going to treat it like a string.
					$querystring = $data; 
				}

				$url .= '?'.$querystring;
			}
		}

		$ch = curl_init( $url )or die( 'Could not Init CURL!' );

		// If requred auth, then auth. 	
		if ( !empty( $this->username ) && !empty( $this->password ) ) {
			curl_setopt ( $ch, CURLOPT_USERPWD, $this->username.':'.$this->password);
		}

		//Check for HTTP Headers. 
		if( !empty( $this->headers ) ) {
			//print_r( $this->headers );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $this->headers );
		}

		/* If you want to save cookies, uncomment and configure this
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
				$post_str = http_build_query( $data, '', '&' );

				$this->post_string = $post_str;
				$this->last_query = $url;
				
				curl_setopt( $ch, CURLOPT_URL, $url );
				curl_setopt( $ch, CURLOPT_POST, count( $data ) );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $this->post_string );
			} else {
				//Trest it like a string.
				curl_setopt( $ch, CURLOPT_POST, 1 ); 
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
			}
		}

		try {
			$response = curl_exec( $ch );
			if( $response === false ) {
				if( $this->retries < $this->max_retries ) { 
					$this->retries++;
					$this->request_data( $url, $format, $method, $data, $decode );
				} else {
					$this->error = curl_error( $ch );
					throw new Exception( $this->error );
				}
			} else { 
				$r_info = curl_getinfo( $ch );
				$this->last_response = $this->response;
				$this->response	= $response;

				if( $r_info['http_code'] == 200 ) {
					switch( $this->format ) {
						case 'json':
							$response = ( $decode ) ? json_decode( $response ) : $response; 
							return( $response );
						break; 
				
						case 'xml':
							if( $decode ) { 
								if( function_exists( 'simplexml_load_string' ) ) {
									$response = @simplexml_load_string( $response );
								} else {
									return( $response );
								}
							}

							return( $response );
						break;
				
						default: 
							return( $response );
						break;
					}
				} else {
					$this->last_response = $this->response;
					$this->response = "SimpleAPI/HTTP Error # $r_info[http_code] <br /> $response -- ".curl_error( $ch ); 
				}
			}
		} catch( Exception $e ) {
			throw new Exception( 'Could not execture cURL' );
		}

		curl_close( $ch );
	}

	public function get_user( ) { return( $this->username ); }
	public function set_user( $username ) { $this->username = $username; }

	public function set_password( $password ) { $this->password = $password; }
	public function get_password( ) { return( $this->password ); }

	public function set_useragent( $agent ) { $this->useragent = $agent; }
	public function get_useragent( ) { return( $this->useragent ); }
	public function last_query( ) { return( $this->last_query ); }
	public function last_response( ) { return( $this->last_response ); }

	public function set_timeout( $timeout ) {
		if( is_int( $timeout ) ) { 
			$this->timeout = (int)$timeout;
		} else { 
			return;
		}
	}

	public function debug_me( ) {
		print_r( $this );
	}
}

?>
