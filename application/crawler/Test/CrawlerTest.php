<?php
namespace Spatie\Crawler\Test;
use GuzzleHttp\Psr7\Uri;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlProfile;
use Psr\Http\Message\UriInterface;
use Spatie\Browsershot\Browsershot;
use Spatie\Crawler\CrawlSubdomains;
use Spatie\Crawler\CrawlInternalUrls;
class CrawlerTest extends TestCase
{


    /** @test */
    public function it_will_crawl_all_found_urls(  $logName   )
    {
        Crawler::create()
            ->setCrawlObserver(new CrawlerSave1() )
            ->ignoreRobots()
            ->setDelayBetweenRequests(100)
            ->startCrawling('http://www.suduak.com');
//        $this->assertCrawledOnce($this->regularUrls());
//        $this->assertNotCrawled($this->javascriptInjectedUrls());
    }
    protected function javascriptInjectedUrls(): array
    {
        return [
            ['url' => 'http://www.suduak.com/javascript', 'foundOn' => 'http://www.suduak.com/link1'],
        ];
    }
    public function getLogContents(): string
    {
        return file_get_contents(static::$logPath);
    }
    protected function assertCrawledOnce($urls)
    {
        $logContent = $this->getLogContents();
        foreach ($urls as $url) {
            $logMessage = "hasBeenCrawled: {$url['url']}";
            if (isset($url['foundOn'])) {
                $logMessage .= " - found on {$url['foundOn']}";
            }
            $logMessage .= PHP_EOL;
//            $this->assertEquals(1, substr_count($logContent, $logMessage), "Did not find {$logMessage} exactly one time in the log but ".substr_count($logContent, $logMessage)." times. Contents of log\n{$logContent}");
        }
    }
    protected function assertNotCrawled($urls)
    {
        $logContent = $this->getLogContents();
        foreach ($urls as $url) {
            $logMessage = "hasBeenCrawled: {$url['url']}";
            if (isset($url['foundOn'])) {
                $logMessage .= " - found on {$url['foundOn']}";
            }
            $logMessage .= PHP_EOL;
            $this->assertEquals(0, substr_count($logContent, $logMessage), "Did find {$logMessage} in the log");
        }
    }
    protected function assertCrawledUrlCount(int $count)
    {
        $logContent = file_get_contents(static::$logPath);
        $actualCount = substr_count($logContent, 'hasBeenCrawled');
        $this->assertEquals($count, $actualCount, "Crawled `{$actualCount}` urls instead of the expected {$count}");
    }
    public function resetLog()
    {

    }

}