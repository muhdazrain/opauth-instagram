<?php
/**
 * Instagram strategy for Opauth
 * based on http://instagr.am/developer/authentication/
 * 
 * More information on Opauth: http://opauth.org
 * 
 * @copyright    Copyright © 2012 U-Zyn Chua (http://uzyn.com)
 * @link         http://opauth.org
 * @package      Opauth.InstagramStrategy
 * @license      MIT License
 */
 
class InstagramStrategy extends OpauthStrategy {
	/**
	 * Compulsory config keys, listed as unassociative arrays
	 * eg. array('app_id', 'app_secret');
	 */
	public $expects = array('client_id', 'client_secret');
	
	/**
	 * Optional config keys with respective default values, listed as associative arrays
	 * eg. array('scope' => 'email');
	 */
	public $defaults = array(
		'redirect_uri' => '{complete_url_to_strategy}int_callback'
	);
	
	/**
	 * Auth request
	 */
	public function request(){
		$url = 'https://api.instagram.com/oauth/authorize/';
		
		$params = array(
			'client_id' => $this->strategy['client_id'],
			'redirect_uri' => $this->strategy['redirect_uri'],
			'response_type' => 'code'
		);

		if (!empty($this->strategy['scope']))
			$params['scope'] = $this->strategy['scope'];
		if (!empty($this->strategy['response_type']))
			$params['response_type'] = $this->strategy['response_type'];
		
		// redirect to generated url
		$this->clientGet($url, $params);
	}
	
	/**
	 * Internal callback, after Instagram's OAuth
	 */
	public function int_callback(){
		if (array_key_exists('code', $_GET) && !empty($_GET['code'])){
			$url = 'https://api.instagram.com/oauth/access_token';
			
			$params = array(
				'client_id' =>$this->strategy['client_id'],
				'client_secret' => $this->strategy['client_secret'],
				'redirect_uri'=> $this->strategy['redirect_uri'],
				'grant_type' => 'authorization_code',
				'code' => trim($_GET['code'])
			);
			$response = $this->serverPost($url, $params, null, $headers);
			
			$results = json_decode($response);
			
			if (!empty($results) && !empty($results->access_token)){
				$userinfo = $this->userinfo($results->user->id, $results->access_token);
				
				$this->auth = array(
					'provider' => 'Instagram',
					'uid' => $userinfo->id,
					'info' => array(
						'name' => $userinfo->full_name,
						'nickname' => $userinfo->username,
						'image' => $userinfo->profile_picture
					),
					'credentials' => array(
						'token' => $results->access_token,
						//'expires' => date('c', time() + $results->expires_in)
					),
					'raw' => $userinfo
				);
				
				if (!empty($userinfo->website)) $this->auth['info']['urls']['website'] = $userinfo->website;
				if (!empty($userinfo->bio)) $this->auth['info']['description'] = $userinfo->bio;
				
				/**
				 * NOTE:
				 * Instagram's access_token have no explicit expiry, however, please do not assume your 
				 * access_token is valid forever.
				 *
				 * Missing optional info values
				 * - email
				 */
				
				$this->callback();
			}
			else{
				$error = array(
					'provider' => 'Instagram',
					'code' => 'access_token_error',
					'message' => 'Failed when attempting to obtain access token',
					'raw' => array(
						'response' => $userinfo,
						'headers' => $headers
					)
				);

				$this->errorCallback($error);
			}
		}
		else{
			$error = array(
				'provider' => 'Instagram',
				'code' => $_GET['error'],
				'reason' => $_GET['error_reason'],
				'message' => $_GET['error_description'],
				'raw' => $_GET
			);
			
			$this->errorCallback($error);
		}
	}
	
	/**
	 * Queries Instagram API for user info
	 *
	 * @param	integer	$uid 
	 * @param	string	$access_token 
	 * @return	array	Parsed JSON results
	 */
	private function userinfo($uid, $access_token){
		$userinfo = $this->serverGet('https://api.instagram.com/v1/users/'.$uid.'/', array('access_token' => $access_token), null, $headers);
		
		if (!empty($userinfo)){
			$results = json_decode($userinfo);
			
			return $results->data;
		}
		else{
			$error = array(
				'provider' => 'Instagram',
				'code' => 'userinfo_error',
				'message' => 'Failed when attempting to query for user information',
				'raw' => array(
					'response' => $userinfo,
					'headers' => $headers
				)
			);

			$this->errorCallback($error);
		}
	}
}
?>