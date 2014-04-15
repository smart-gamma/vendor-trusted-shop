<?php
namespace Gamma\TrustedShop\TrustedShopBundle\Listener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Localdev\FrameworkExtraBundle\Services\Service;

class KernelEvents extends Service
{
	public function onKernelRequest(GetResponseEvent $event)
	{
		if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType())
		{
			/* @var  $globals \Twig_Environment */
			$globals = $this->container->get('twig');
            $vars = $globals->getGlobals();
            if(!isset($vars['_reviewAggregation'])) {
                /* @var $trustedShopManager \Gamma\TrustedShop\TrustedShopBundle\Services\TrustedShopManager */
                $trustedShopManager = $this->container->get('gamma.trusted_shop.manager');
                $reviewAggregation = $trustedShopManager->getReviewAggregation();
                $globals->addGlobal('_reviewAggregation', $reviewAggregation);
            }    
		}
	}
}
