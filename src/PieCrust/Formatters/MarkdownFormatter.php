<?php

namespace PieCrust\Formatters;

use League\CommonMark\CommonMarkConverter;
use PieCrust\IPieCrust;


class MarkdownFormatter implements IFormatter
{
    protected $pieCrust;
    protected $useExtra;
    protected $useSundown;
    protected $parser;
    protected $parserConfig;

    public function initialize(IPieCrust $pieCrust)
    {
        $this->pieCrust = $pieCrust;
        $this->parser = null;
        #$this->useExtra = $pieCrust->getConfig()->getValue('markdown/use_markdown_extra');
        #$this->useSundown = $pieCrust->getConfig()->getValue('markdown/use_sundown');
        #$this->parserConfig = $pieCrust->getConfig()->getValue('markdown/config');
    }

    public function getPriority()
    {
        return IFormatter::PRIORITY_DEFAULT;
    }

    public function isExclusive()
    {
        return true;
    }

    public function supportsFormat($format)
    {
        return preg_match('/markdown|mdown|mkdn?|md/i', $format);
    }

    public function format($text)
    {
        $converter = new CommonMarkConverter();
        $cnf = $this->pieCrust->getConfig();
        if(isset($cnf['referenceMap'])) {
            $text .= "\n\n";
            foreach($cnf['referenceMap'] as $ref => $url) {
                $text .= "[$ref]: $url\n";
            }
            $text .= "\n";
        }
        $result = $converter->convertToHtml($text);
        return $result;
    }

    private function phpMarkdownFormat($text)
    {
        if ($this->parser == null)
        {
            if ($this->useExtra)
                $this->parser = new MarkdownExtra();
            else
                $this->parser = new Markdown();

            if ($this->parserConfig)
            {
                foreach ($this->parserConfig as $param => $value)
                {
                    $this->parser->$param = $value;
                }
            }
        }

        $this->parser->fn_id_prefix = '';
        $executionContext = $this->pieCrust->getEnvironment()->getExecutionContext();
        if ($executionContext != null)
        {
            $page = $executionContext->getPage();
            if ($page && !$executionContext->isMainPage())
            {
                $footNoteId = $page->getUri();
                $this->parser->fn_id_prefix = $footNoteId . "-";
            }
        }

        return $this->parser->transform($text);
    }

    private function sundownFormat($text)
    {
        if ($this->parser == null)
        {
            $options = $this->parserConfig;
            if ($options == null)
                $options = array();

            $this->parser = new \Sundown\Markdown(
                \Sundown\Render\HTML,
                $options
            );
        }

        return $this->parser->render($text);
    }
}
