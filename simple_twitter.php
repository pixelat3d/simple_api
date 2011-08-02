<?php
	/*
		simple_twitter.php
		Simple Twitter class using simple_api
	*/

require_once( 'simple_api.php' );
class simple_twitter extends simple_api {
	public static $cache_file = 'tweet_cache'; 

	public function user_timeline( $count = 20, $offset = 0 ) {
		$url = 'http://twitter.com/statuses/user_timeline/'.$this->username.'.'.$this->format.'?count='.$count;
		return ( $this->request_data( $url, $this->format, 'get' ) );
	}

	public function public_timeline( $count = 20, $offset = 0 ) {
		$url = 'http://twitter.com/statuses/public_timeline/'.$this->username.'.'.$this->format.'?count='.$count;
		return ( $this->request_data( $url, $this->format ) );
	}

	public function format_timestamp( $timestamp ) {
		$time_bits = explode( ' ', $timestamp ); 
		return( $time_bits[1].', '.$time_bits[2].' '.$time_bits[5] );
	}
}

?>
