<?php

namespace Gamma\TrustedShop\TrustedShopBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Localdev\FrameworkExtraBundle\Services\LoggerService;

/**
 * Trusted Shop service
 *
 * @author Evgeniy Kuzmin <jekccs@gmail.com>
 */
class TrustedShopManager extends LoggerService
{
    /**
     * Trusted Shop API
     *
     * @var Api
     */
    protected $api;
    
    /**
     * Initializes a new instance of the Images class.
     *
     * @param ContainerInterface $container Service container
     * @param Logger             $logger    Logger
     * @param Api                $api       Panthermedia API
     */
    public function __construct(ContainerInterface $container, Logger $logger = null, Api $api)
    {
        parent::__construct($container, $logger);
        $this->api = $api;
    }

    /**
     * Get trusted shop review aggregation
     *
     * @param Image $image  Image to process
     */
    public function getReviewAggregation()
    {
        try {
            $review = $this->api->reviewAggregation();
            return $review;
        } catch (\Exception $ex) {
            $this->getLogger()->addError($ex);
        }
    }
}
