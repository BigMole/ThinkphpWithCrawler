<?php
namespace Spatie\Crawler\Test;

use Spatie\Crawler\CrawlObserver;
use Spatie\Crawler\CrawlFile;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

class CrawlerSave1 extends CrawlObserver
{
    /** @var string */
    protected $observerId ;
    public $file;
    public $dateTime;
    public $filePath='runtime';


    public function __construct( $observerId=''  )
    {
        $this->datetime=date('Y-m-d',time());

        $this->observerId = $observerId??'task'.$this->dateTime;

        $this->file=new CrawlerFile();

    }

    public function getLogPath(){

        $logPath  =$this->filePath;
        $logPath .='/';
        $logPath .= $this->dateTime;
        $logPath .='/';
        if(!is_dir($logPath)){
            $this->file->create_dir($logPath);
        }
        return $logPath;
    }

    /**
     * Called when the crawler will crawl the url.
     *
     * @param \Psr\Http\Message\UriInterface   $url
     */
    public function willCrawl(UriInterface $url)
    {

        $logPath=$this->getLogPath();
        $fileName='willCrawler'.$this->observerId;
        $wholePath=$logPath.$fileName;

        $time=date('H:i:s',time());
        CrawlerTest::log($wholePath,$time."-willCrawl: {$url}");


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
        $logPath=$this->getLogPath();
        $fileName='afterCrawlered'.$this->observerId;
        $wholePath=$logPath.$fileName;
        $time=date('H:i:s',time());


        $logText = "{$this->observerId}hasBeenCrawled: {$url}".PHP_EOL;
        if ((string) $foundOnUrl) {
            if( $response instanceof ResponseInterface ){
                $logText .= " - found body on {$foundOnUrl}\n".(string)$response->getBody();
            }else{
                echo $logText;
                $logText .= " - found on {$foundOnUrl}";
            }

        }
        CrawlerTest::log($wholePath,$logText);
    }
    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling()

    {   $logPath=$this->getLogPath();
        $fileName='afterCrawlered'.$this->observerId;
        $wholePath=$logPath.$fileName;
        $time=date('H:i:s',time());
        CrawlerTest::log($wholePath,"{$time}-{$this->observerId}finished crawling");
    }
}