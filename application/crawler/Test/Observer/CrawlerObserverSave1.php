<?php
namespace Spatie\Crawler\Test\Observer;


use Spatie\Crawler\CrawlObserver;
use Spatie\Crawler\CrawlFile;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Spatie\Crawler\Test\CrawlerTest;

class CrawlerObserverSave1 extends CrawlObserver
{
    /** @var string */
    protected $observerId ;
    public $file;
    public $dateTime;
    public $filePath='E:\\logs\\';


    public function __construct( $observerId  )
    {
        $this->dateTime = date('Y-m-d',time());

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
    public function getLogPrefix(){
        return (time()/(6*6*24) )%100;// 一天分为100个日志
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
        $time=date('H:i:s',time());
        $logText = "{$time}:{$this->observerId}hasBeenCrawled: {$url}".PHP_EOL;
        if ( (string) $foundOnUrl ) {
            $logText .= " - found on {$foundOnUrl}".PHP_EOL;

        }
        if($response->getStatusCode() =='200'){
            $logText .= (string)$response->getBody();
        }else{
            $logText .= 'Code:'.$response->getStatusCode();
        }




        $this->logCrawl($url, $foundOnUrl ,$logText);


    }
    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null
    ) {
        $this->logCrawl($url, $foundOnUrl ,$requestException );
    }
    protected function logCrawl(UriInterface $url, ?UriInterface $foundOnUrl,  $logText)
    {
        //文件名路径生成
        $logPath=$this->getLogPath();
        $fileName='afterCrawlered'.$this->observerId.'_'.$this->getLogPrefix();
        $wholePath=$logPath.$fileName;
        CrawlerTest::log($wholePath,$logText);
    }
    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling()
    {   $logPath=$this->getLogPath();
        $fileName='afterCrawlered'.$this->observerId.'_'.$this->getLogPrefix();
        $wholePath=$logPath.$fileName;
        $time=date('H:i:s',time());
        CrawlerTest::log($wholePath,"{$time}-{$this->observerId}finished crawling");
    }
}