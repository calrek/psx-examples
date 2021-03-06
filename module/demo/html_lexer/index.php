<?php

namespace demo\html_lexer;

use PSX\Filter;
use PSX\Html\Lexer;
use PSX\Http;
use PSX\Http\GetRequest;
use PSX\Module\ViewAbstract;

class index extends ViewAbstract
{
	public function onLoad()
	{
		$this->template->set(str_replace('\\', DIRECTORY_SEPARATOR, __CLASS__) . '.tpl');
	}

	public function onPost()
	{
		$src = $this->getBody()->src('string', array(new Filter\Length(6, 256), new Filter\Url()));

		if(!$this->getValidator()->hasError())
		{
			$http     = new Http();
			$request  = new GetRequest($src);
			$request->setFollowLocation(true);
			$response = $http->request($request);

			$root     = Lexer::parse($response->getBody());
			$elements = $root->getElementsByTagName('a');
			$links    = array();

			foreach($elements as $el)
			{
				$href = $el->getAttribute('href');

				if(!empty($href))
				{
					$links[] = $href;
				}
			}

			$this->template->assign('src', $src);
			$this->template->assign('links', $links);
		}
		else
		{
			$this->template->assign('error', $this->getValidator()->getError());
		}
	}
}
