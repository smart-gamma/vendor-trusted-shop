<?php

namespace Gamma\TrustedShop\TrustedShopBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Cache\CacheProvider;

use Localdev\FrameworkExtraBundle\Services\LoggerService;


/**
 * Interface to the trusted shop api
 *
 * @author Evgeniy Kuzmin <jekccs@gmail.com>
 */
class Api extends LoggerService
{
    const REVIEW_PROVIDER = 'TrustedShop';
    
    /**
     * Host
     *
     * @var string
     */
    protected $host = null;

    /**
     * api version
     *
     * @var int
     */
    protected $version = 1;
    
    /**
     * Trusted Shop Id 
     *
     * @var int
     */
    protected $TSID = null;

    /**
     * Max rank point
     *
     * @var int
     */
    protected $maxRank = 5;

    /**
     * Cache driver
     *
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    protected $cache;

    /**
     * Cache time out (default 12 hours)
     *
     * @var int
     */
    protected $cacheTimeOut = 43200;
    
    /**
     * {@inheritDoc}
     */
    public function __construct(ContainerInterface $container,CacheProvider $cache)
    {
        parent::__construct($container);
        
        $this->scheme = $container->getParameter('gamma.trusted_shop.config.scheme');
        $this->host = $container->getParameter('gamma.trusted_shop.config.host');
        $this->path = $container->getParameter('gamma.trusted_shop.config.path');
        $this->version = $container->getParameter('gamma.trusted_shop.config.api_version');
        $this->tsid = $container->getParameter('gamma.trusted_shop.config.tsid');
        $this->maxRank = $container->getParameter('gamma.trusted_shop.config.max_rank');
        $this->cacheTimeOut = $container->getParameter('gamma.trusted_shop.config.cache_timeout');
        
        $this->cache = $cache;
        $this->container = $container;
    }

    /**
     * Load aggregated reviews data
     *
     * @return array
     */
    public function reviewAggregation()
    {
        $data = array();
        $apiUrl = $this->scheme.'://'.$this->host.'/'.$this->path.'ratings/v'.$this->version.'/'.$this->tsid.'.xml';
        $string = $this->call($apiUrl);
 
        if ($xml = simplexml_load_string($string)) {
            $xPath = "/shop/ratings/result[@name='average']";
            $data['averageRank'] = (float) $xml -> xpath($xPath)[0];
            $data['maxRank'] = $this->maxRank;
            $data['votes'] = $xml->ratings["amount"];
            $data['shopName'] = $xml->name;
            $data['ReviewProvider'] = self::REVIEW_PROVIDER;
        }

        return $data;
    }

    /**
     * Load aggregated reviews data
     *
     * @return array
     */
    public function reviews()
    {
        $reviews = array();
        $apiUrl = $this->scheme.'://'.$this->host.'/'.$this->path.'ratings/v'.$this->version.'/'.$this->tsid.'.xml';
        $xmlString = $this->call($apiUrl);
 
        if ($xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA)) {               
            $xPath = "ratings/opinions/opinion";
            $options =  $xml -> xpath($xPath);
            $i = 0;
            foreach($options as $option) {
                $reviews[$i]['rating'] = (int)$option->rating[0];
                $reviews[$i]['comment'] = trim((string)$option->comment);
                $reviews[$i]['date'] = (string)$option->date;
                $reviews[$i]['reply'] = trim((string)$option->reaction->reply);
                $reviews[$i]['provider'] = self::REVIEW_PROVIDER;
                $i++;
            }
        }

        return $reviews;
    }
    
    /**
     * Request to api
     * @param type $apiUrl
     * @return string
     */
    private function call($apiUrl)
    {
        $id = $this->getCachePath($apiUrl);

        if ($this->cache->contains($id)) {
            $output = $this->cache->fetch($id);  
        } else {    
            // load fresh from API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_URL, $apiUrl);

            $output = curl_exec($ch);
            curl_close($ch);
            
            $this->cache->save($id, $output, $this->cacheTimeOut);
        } 
        
        return $output;
    }
    
    /**
     * Get cache storage id
     * @param type $apiUrl
     * @return string
     */
    private function getCachePath($apiUrl)
    {
        return self::REVIEW_PROVIDER.'_'.md5($apiUrl);
    }
}
