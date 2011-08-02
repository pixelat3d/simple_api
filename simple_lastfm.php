<?php
	/*
		simple_lastfm.php
		Simple last.fm Class using simple_api
	*/

require_once( 'simple_api.php' );
class simple_lastfm extends simple_api {
	private $apikey = 'b25b959554ed76058ac220b7b2e0a026'; //Taken from API spec page. Not sure if still works.
	public function recent_tracks( $count = 10, $offset = 0 ) {
		$url = 	'http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user='.$this->username.
			'&api_key='.$this->apikey.'&limit='.$count.'&nowplaying=false';
		$url .= ( $this->format !== 'xml' ) ? "&format=$this->format" : ''; 
		return( $this->request_data( $url, $this->format, 'get' ) );
	}

	public function set_apikey( $apikey ) { $this->apikey = $apikey; }
	public function get_apikey( ) { return( $this->apikey ); }
}

?>
