<?php

class access_token extends PSX_Module_ViewAbstract
{
	private $http;
	private $oauth;
	private $session;
	private $validate;
	private $post;

	public function onLoad()
	{
		$this->http     = new PSX_Http(new PSX_Http_Handler_Curl());
		$this->oauth    = new PSX_Oauth($this->http);
		$this->validate = $this->getValidator();
		$this->post     = $this->getBody();

		$this->session  = new PSX_Session('oc');
		$this->session->start();

		$this->template->set('demo/oauth_consumer/' . __CLASS__ . '.tpl');
	}

	public function onGet()
	{
		$this->template->assign('oc_consumer_key', $this->session->get('oc_consumer_key'));
		$this->template->assign('oc_consumer_secret', $this->session->get('oc_consumer_secret'));
		$this->template->assign('oc_token', $this->session->get('oc_token'));
		$this->template->assign('oc_token_secret', $this->session->get('oc_token_secret'));
		$this->template->assign('oc_verifier', $this->session->get('oc_verifier'));

		$this->template->assign('ui_status', 0x0);
	}

	public function onPost()
	{
		$consumerKey    = $this->session->get('oc_consumer_key');
		$consumerSecret = $this->session->get('oc_consumer_secret');
		$token          = $this->session->get('oc_token');
		$tokenSecret    = $this->session->get('oc_token_secret');
		$verifier       = $this->session->get('oc_verifier');

		$url = $this->post->url('string', array(new PSX_Filter_Length(3, 256), new PSX_Filter_Url()));

		if(!$this->validate->hasError())
		{
			$url = new PSX_Url($url);

			$response = $this->oauth->accessToken($url, $consumerKey, $consumerSecret, $token, $tokenSecret, $verifier);

			$this->template->assign('request', $this->http->getRequest());
			$this->template->assign('response', $this->http->getResponse());

			$token       = $response->getToken();
			$tokenSecret = $response->getTokenSecret();

			if(!empty($token) && !empty($tokenSecret))
			{
				$this->session->set('oc_token', $token);
				$this->session->set('oc_token_secret', $tokenSecret);
				$this->session->set('oc_authed', true);

				$this->template->assign('token', $token);
				$this->template->assign('token_secret', $tokenSecret);
			}
			else
			{
				$this->template->assign('token', '');
				$this->template->assign('token_secret', '');
			}
		}
		else
		{
			$this->template->assign('error', $this->validate->getError());
		}

		$this->template->assign('ui_status', 0x1);
	}
}