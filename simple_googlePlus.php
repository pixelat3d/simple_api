<?php

require_once( 'simple_api.php' );
class simple_googlePlus extends simple_api {
	private $apiKey = 'AIzaSyBXHE7EUWfw2t14SFcfKB4QZffWXls8u8w';
	private $userId = '104727849581770155152';
	protected $format = 'json';
	protected $scope = 'public';

	public function __construct( $userId = '' ) { 
		if( !empty( $userId ) ) {
			$this->userId = $userId;
		}
	}

	public function recent_posts( $count = 10, $offset = 0, $pageToken = '' ) {
		$page = ($offset / $count );
		$page = ( $page <= 0 ) ? 1 : abs( $page );

		$url = 'https://www.googleapis.com/plus/v1/people/'.$this->userId.'/activities/'.$this->scope.
		'?alt=json&maxResults='.$count.'&pp='.$page.'&key=';
		$url .= ( empty( $this->apiKey ) ) ?  '' : $this->apiKey;
		$response = $this->request_data( $url, $this->format, 'get' );
		return( $response );
	}
}

?>
