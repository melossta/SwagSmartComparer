<?php declare(strict_types=1);

namespace SwagSmartComparer\Storefront\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]

class CompareController extends StorefrontController
{
    private EntityRepository $productRepository;

    public function __construct(EntityRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Compare Page
     */
    #[Route(path: '/compare', name: 'frontend.smc.compare.page', methods: ['GET'])]
    public function comparePage(SessionInterface $session, SalesChannelContext $context): Response
    {
        $ids = $session->get('smc_compare', []);

        $products = [];
        if (!empty($ids)) {
            $criteria = (new Criteria($ids))
                ->addAssociation('manufacturer')
                ->addAssociation('cover.media')
                ->addAssociation('properties.group')
                ->addAssociation('currency')
                        ->addAssociation('prices') // Add price association
            ->addAssociation('calculatedPrice'); // Add calculated price association
            ;



            $products = $this->productRepository->search($criteria, $context->getContext())->getEntities();
        }

        return $this->renderStorefront('@Storefront/storefront/page/compare/index.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * Add product to compare list
     */
    #[Route(path: '/compare/add/{productId}', name: 'frontend.smc.compare.add', methods: ['POST'])]
    public function addProduct(string $productId, SessionInterface $session): Response
    {
        $ids = $session->get('smc_compare', []);
        if (!in_array($productId, $ids, true)) {
            $ids[] = $productId;
        }
        $session->set('smc_compare', $ids);

        return $this->redirectToRoute('frontend.smc.compare.page');
    }

    /**
     * Remove product from compare list
     */
    #[Route(path: '/compare/remove/{productId}', name: 'frontend.smc.compare.remove', methods: ['POST'])]
    public function removeProduct(string $productId, SessionInterface $session): Response
    {
        $ids = array_filter($session->get('smc_compare', []), fn ($id) => $id !== $productId);
        $session->set('smc_compare', $ids);

        return $this->redirectToRoute('frontend.smc.compare.page');
    }
}
