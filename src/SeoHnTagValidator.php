<?php

namespace Globalis\SeoHnTagValidator;

use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;
use Spatie\Crawler\Crawler as Cra;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;

class SeoHnTagValidator
{
    protected $res;
    function __construct()
    {
    }

    private function countH1($tab) {
        $nb = 0;
        
        foreach($tab as $val){
            $val["tag"] =='h1' ? $nb++ : $nb;
        }
        return $nb;
    }

    private function getLevel($elm) {
        return $elm['tag'][1];
    }

    private function isSorted($hN) {
        if(count($hN) !== 0) if($this->getLevel($hN[0]) != 1) return 0;
        $i = 1;
        while ($i < count($hN)) {       
            if($this->getLevel($hN[$i-1]) === $this->getLevel($hN[$i])) $i++;
            else { 
                if ($this->getLevel($hN[$i-1]) + 1 == $this->getLevel($hN[$i])) $i++;
                else { 
                    if($this->getLevel($hN[$i-1]) >= $this->getLevel($hN[$i]) ) $i++; 
                    else return 0;
                }
            }
        }
        return 1;
    }

    function validateUrl($url) {
        $hN = [];
        $errors = [];
        $this->res["url"] = $url;
        $client = new Client(['base_uri' => $url]);
        $html = $client->request('GET', $url)->getBody()->getContents();

        $crawler = new Crawler($html);
        $crawler = $crawler->filter('*');
        foreach ($crawler as $domElement) {      
            if(preg_match('/h[1-6]/',$domElement->nodeName) === 1) {
                array_push($hN, ["tag" => $domElement->nodeName,"value" => $domElement->textContent]);
            }
        }
        
        if($this->countH1($hN) !== 1) { 
           if($this->countH1($hN) === 0) array_push($errors,"missing-h1");
           else array_push($errors,"multiples-h1");
        }
        if($this->isSorted($hN) === 0) array_push($errors,"wrong-hn-order");
        $this->res["errors"] = $errors;
        count($this->res["errors"]) !== 0 ? $this->res["is_valid"] = "false" : $this->res["is_valid"] = "true";
        $this->res["tags"] = $hN;

        return $this->res;
    }


    function validateWebSite($url, $onlyErrors = 0, $concurrent_requests = 3) {
        $globalRes = [];
        $crawlerObserver = new PageCrawlObserver();

        Cra::create()
        ->setCrawlObserver($crawlerObserver)
        ->setCrawlProfile(new CrawlInternalUrls($url))
        ->ignoreRobots()
        ->acceptNofollowLinks()
        ->setParseableMimeTypes(['text/html'])
        ->setUserAgent('SeoHnTagValidator')
        ->setConcurrency($concurrent_requests)
        ->startCrawling($url);

        $result = $crawlerObserver->getResult();

        foreach ($result as $value) {
                $res = $this->validateUrl($value["path"]);
                if($onlyErrors === 0)  array_push($globalRes,$res);
                else if($res['is_valid'] === "false") array_push($globalRes,$res);
        }
        return $globalRes;
    }
}