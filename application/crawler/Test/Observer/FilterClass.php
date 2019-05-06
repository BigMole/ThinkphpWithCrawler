<?php
namespace Spatie\Crawler\Test\Observer;

use Spatie\Crawler\CrawlFile;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Spatie\Crawler\Test\CrawlerTest;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use think\Exception;

/**
 * Class FilterClass
 * @package Spatie\Crawler
 * 用来获取过滤页面内容
 */

class FilterClass{
    /** @var string */
    protected $observerId ;
    private $conditionArrs=[];
    private $urlsArr=[];
    public $file;
    public $dateTime;
    public $filePath='E:\\logs\\';



	//执行类 ---TODO放入数据库中进行存储
	
	public function generateCondition(){
		
		//并集 数组集合内是或的关系
		$this->conditionArrs=[
			
			[	//单条内部是且的关系
				'idName'=>'',//id  topics
				'tagName'=>'div',	//html元素名称
				'className'=>'post',	//类名
				'keyWord'=>'',	
			],
			
		
		
		];										
		
	}

    /**
     * 对于条件数组进行组合
     * @param $conditionArr 对于条件数组
     *
     *
     * @return 返回组合而成的xpath表达式
     */

	public function mergeStr($conditionArr){


		$tagStr=!empty($conditionArr['tagName'])?'//'.$conditionArr['tagName']:'';
		$idStr=!empty($conditionArr['idName'])?'['.'@id="'.$conditionArr['idName'].'"]':'';
		$classStr=!empty($conditionArr['className'])?'['.'@class="'.$conditionArr['className'].'"]':'';

        $returnStr = $tagStr.$classStr.$idStr;

		return $returnStr;
	}


    /**
     * 对于数据进行过滤
     * @return string
     *
     *
     */

    public function filter($html , $foundOnUrl ){
        //初始化
        $domCrawler = new DomCrawler( $html  , $foundOnUrl);
        $text='';


        //对应于每一个条件进行组合xpath条件是
		foreach($this->conditionArrs as $k=>$v){

            //生成xpath对应 选择语句
            $xpathRegStr=$this->mergeStr($v) ;
            //生成 DOM选择后的对象
            $domCrawlerNodes=$domCrawler->filterXpath($xpathRegStr);

//            halt($domCrawlerNodes->html());
            // 如果node list 为空  会报exception 所以提前判断

            if($domCrawlerNodes ->count()>0){
                $text.=$domCrawlerNodes->html();
            }
        $text.="\n\r";
			
		}

		return $text;
		
    }


    public function __construct( $observerId  )
    {
        $this->dateTime = date('Y-m-d',time());

        $this->observerId = $observerId??'task'.$this->dateTime;

        $this->file=new CrawlerFile();

        //加入过滤条件
        $this->generateCondition();

    }



    public function getLogPath(){

        $logPath  =$this->filePath;
        $logPath .='/';
        $logPath .= $this->dateTime;
        $logPath .='/';
        $logPath .=$this->observerId;
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

            $logText .= $this->filter((string)$response->getBody(), $foundOnUrl ) ;

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
