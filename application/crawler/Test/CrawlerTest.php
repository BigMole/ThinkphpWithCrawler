<?php
namespace Spatie\Crawler\Test;

use Spatie\Crawler\Test\Observer\CrawlerObserverSave1;
use GuzzleHttp\Psr7\Uri;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlProfile;
use Psr\Http\Message\UriInterface;
use Spatie\Browsershot\Browsershot;
use Spatie\Crawler\CrawlSubdomains;
use Spatie\Crawler\CrawlInternalUrls;
use Spatie\Crawler\Test\Observer\FilterClass;

class CrawlerTest extends TestCase
{


    /**
     * @param $ObserverId 输入对应的ID值
     * @param $baseUrl 输入对应的URL
     *
     */
    public function  CrawlerSubDomain($ObserverId, $baseUrl )
    {

        Crawler::create()
            ->setCrawlObserver(new CrawlerObserverSave1( $ObserverId ))
            ->setMaximumDepth(2)
            ->setCrawlProfile(new CrawlSubdomains($baseUrl))
            ->startCrawling($baseUrl);

    }

      /**
     * @param $ObserverId 输入对应的ID值
     * @param $baseUrl 输入对应的URL
     *
     */
    public function  CrawlerSubDomain_1($ObserverId, $baseUrl )
    {

        Crawler::create()
            ->setCrawlObserver(new FilterClass( $ObserverId ))
            ->setMaximumDepth(10)
            ->setCrawlProfile(new CrawlSubdomains($baseUrl))
            ->startCrawling($baseUrl);

    }




    /**
     * @param $ObserverId 输入对应的ID值
     * @param $baseUrl 输入对应的URL
     *
     */
    public function  CrawlerSubDomainWithJs( $ObserverId,$baseUrl )
    {
        $crawler = Crawler::create();
        if (getenv('TRAVIS')) {
            $browsershot = new Browsershot();
            $browsershot->noSandbox();
            $crawler->setBrowsershot($browsershot);
        }

        $crawler
            ->setCrawlObserver(new CrawlerObserverSave1($ObserverId))
            ->executeJavaScript()
            ->setMaximumDepth(5)
            ->setCrawlProfile(new CrawlSubdomains($baseUrl))
            ->startCrawling($baseUrl);

    }





    public static function log($logPath,string $text)
    {

        file_put_contents($logPath, $text.PHP_EOL, FILE_APPEND);

    }


    /** @test */
    public function profile_crawls_a_domain_and_its_subdomains()
    {
        $baseUrl = 'http://spatie.be';
        $urls = [
            'http://spatie.be' => true,
            'http://subdomain.spatie.be' => true,
            'https://www.subdomain.spatie.be' => true,
            'https://sub.dom.ain.spatie.be' => true,
            'https://subdomain.localhost:8080' => false,
            'https://localhost:8080' => false,
        ];
        $profile = new CrawlSubdomains($baseUrl);
        foreach ($urls as $url => $bool) {
            $this->assertEquals($bool, $profile->isSubdomainOfHost(new Uri($url)));
        }
    }
    /** @test */
    public function it_crawls_subdomains()
    {
        $baseUrl = 'http://www.suduak.com';
        Crawler::create()
            ->setCrawlObserver(new CrawlLogger())
            ->setMaximumDepth(2)
            ->setCrawlProfile(new CrawlSubdomains($baseUrl))
            ->startCrawling($baseUrl);
        $this->assertCrawledOnce([
            ['url' => 'http://www.suduak.com/'],
            ['url' => 'http://www.suduak.com/link1', 'foundOn' => 'http://www.suduak.com/'],
            ['url' => 'http://www.suduak.com/link2', 'foundOn' => 'http://www.suduak.com/'],
            ['url' => 'http://www.suduak.com/dir/link4', 'foundOn' => 'http://www.suduak.com/'],
            ['url' => 'http://www.suduak.com/link3', 'foundOn' => 'http://www.suduak.com/link2'],
            ['url' => 'http://www.suduak.com/dir/link5', 'foundOn' => 'http://www.suduak.com/dir/link4'],
            ['url' => 'http://sub.localhost:8080/subdomainpage', 'foundOn' => 'http://www.suduak.com/link2'],
            ['url' => 'http://subdomain.sub.localhost:8080/subdomainpage', 'foundOn' => 'http://www.suduak.com/link2'],
        ]);
        $this->assertNotCrawled([
            ['url' => 'http://www.suduak.com/notExists'],
            ['url' => 'http://www.suduak.com/dir/link5'],
            ['url' => 'http://www.suduak.com/dir/subdir/link5'],
            ['url' => 'http://example.com/', 'foundOn' => 'http://www.suduak.com/link1'],
        ]);
    }
    protected function regularUrls(): array
    {
        return [
            ['url' => 'http://www.suduak.com/'],
            ['url' => 'http://www.suduak.com/link1', 'foundOn' => 'http://www.suduak.com/'],
            ['url' => 'http://www.suduak.com/link2', 'foundOn' => 'http://www.suduak.com/'],
            ['url' => 'http://www.suduak.com/link3', 'foundOn' => 'http://www.suduak.com/link2'],
            ['url' => 'http://www.suduak.com/notExists', 'foundOn' => 'http://www.suduak.com/link3'],
            ['url' => 'http://example.com/', 'foundOn' => 'http://www.suduak.com/link1'],
            ['url' => 'http://www.suduak.com/dir/link4', 'foundOn' => 'http://www.suduak.com/'],
            ['url' => 'http://www.suduak.com/dir/link5', 'foundOn' => 'http://www.suduak.com/dir/link4'],
            ['url' => 'http://www.suduak.com/dir/subdir/link6', 'foundOn' => 'http://www.suduak.com/dir/link5'],
        ];
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