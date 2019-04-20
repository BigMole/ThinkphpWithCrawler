<?php
namespace Spatie\Crawler\Test\Observer;
use Spatie\Crawler\CrawlObserver;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
class CrawlLogger extends CrawlObserver
{
    /** @var string */
    protected $observerId ;
    public function __construct(string $observerId  )
    {
        if(!$observerId)$observerId='Tasks';
        if ($observerId !== '') {
            $observerId .= ' - ';
        }
        $this->observerId = $observerId;
    }
    /**
     * Called when the crawler will crawl the url.
     *
     * @param \Psr\Http\Message\UriInterface   $url
     */
    public function willCrawl(UriInterface $url)
    {
        CrawlerTest::log("{$this->observerId}willCrawl: {$url}");
     }
    /**
     * Called when the crawler has crawled the given url.
     *
     * @param \Psr\Http\Message\UriInterface $url
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Psr\Http\Message\UriInterface|null $foundOnUrl
     */
    public function crawled(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null
    ) {
        $this->logCrawl($url, $foundOnUrl ,$response);
    }
    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null
    ) {
        $this->logCrawl($url, $foundOnUrl ,$requestException );
    }
    protected function logCrawl(UriInterface $url, ?UriInterface $foundOnUrl,  $response)
    {
        $logText = "{$this->observerId}hasBeenCrawled: {$url}";
        if ((string) $foundOnUrl) {
            if( $response instanceof ResponseInterface ){
                $logText .= " - found body on {$foundOnUrl}".print_r((string) $response->getBody(),true);
            }else{
                echo $logText;
                $logText .= " - found on {$foundOnUrl}";
            }

        }
        CrawlerTest::log($logText);
    }
    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling()
    {
        CrawlerTest::log("{$this->observerId}finished crawling");
    }
}