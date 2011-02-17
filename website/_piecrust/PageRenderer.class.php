<?php

class PageRenderer
{
	protected $pieCrust;
	
	public function __construct(PieCrust $pieCrust)
	{
		$this->pieCrust = $pieCrust;
	}
	
	public function render(Page $page, $extraData = null, $outputHeaders = true)
	{
		$pageConfig = $page->getConfig();
		$templateName = $pageConfig['layout'] . '.html';
		
		// Get the template engine and the page data.
		$templateEngine = $this->pieCrust->getTemplateEngine();
		$data = array('content' => $page->getContents());
		$data = array_merge($data, $page->getPageData(), $this->pieCrust->getSiteData());
		if ($extraData != null)
		{
			if (is_array($extraData))
			{
				$data = array_merge($data, $extraData);
			}
			else
			{
				$data['extra'] = $extraData;
			}
		}
		
		// Set the HTML header and render the page.
		if ($outputHeaders === true)
		{
			switch ($pageConfig['content_type'])
			{
				case 'html':
				default:
					header("Content-type: text/html; charset=utf-8");
					break;
				case 'xml':
					header("Content-type: text/xml; charset=utf-8");
					break;
				case 'txt':
				case 'text':
					header("Content-type: text/plain; charset=utf-8");
					break;
				case 'css':
					header('Content-type: text/css; charset=utf-8');
					break;
				case 'atom':
					header("Content-type: application/atom+xml; charset=utf-8");
					break;
				case 'rss':
					header("Content-type: application/rss+xml; charset=utf-8");
					break;
				case 'json':
					header('Content-type: application/json; charset=utf-8');
					break;
			}
		}
		
		echo "<!-- PieCrust " . PieCrust::VERSION . " - " . ($page->isCached() ? "baked this morning!" : "baked just now!") . " -->\n";
		$templateEngine->renderFile($templateName, $data);
	}
	
	public function get(Page $page, $extraData = null, $outputHeaders = true)
	{
		ob_start();
		$this->render($page, $extraData, $outputHeaders);
		return ob_get_clean();
	}
}
