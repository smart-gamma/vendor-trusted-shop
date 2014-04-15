<?php

namespace Gamma\TrustedShop\TrustedShopBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * {@inheritDoc}
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        
        $this->scheme = $container->getParameter('gamma.trusted_shop.config.scheme');
        $this->host = $container->getParameter('gamma.trusted_shop.config.host');
        $this->path = $container->getParameter('gamma.trusted_shop.config.path');
        $this->version = $container->getParameter('gamma.trusted_shop.config.api_version');
        $this->TSID = $container->getParameter('gamma.trusted_shop.config.TSID');
        $this->maxRank = $container->getParameter('gamma.trusted_shop.config.maxRank');
        
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
        $apiUrl = $this->scheme.'://'.$this->host.'/'.$this->path.'ratings/v'.$this->version.'/'.$this->TSID.'.xml';
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
    
    private function call($apiUrl)
    {
        // check if cached version exists
        //if (!cachecheck($cacheFileName, $cacheTimeOut)) {
            // load fresh from API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_URL, $apiUrl);

            $output = curl_exec($ch);
            curl_close($ch);

            // Write the contents back to the file
            // Make sure you can write to file's destination
            //file_put_contents($cacheFileName, $output);
        //} 
        
        return $output;
    }
}
