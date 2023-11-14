<?php

declare(strict_types=1);

namespace Setono\SyliusGiftCardPlugin\Controller\Action;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Setono\SyliusGiftCardPlugin\Model\GiftCardBalanceCollection;
use Setono\SyliusGiftCardPlugin\Repository\GiftCardRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * The purpose of this class is to show the gift card balance, i.e. what amount is still available on enabled gift cards
 */
final class GiftCardBalanceAction
{
    private GiftCardRepositoryInterface $giftCardRepository;

    private ViewHandlerInterface $viewHandler;

    private ?Environment $twig;

    public function __construct(
        GiftCardRepositoryInterface $giftCardRepository,
        ViewHandlerInterface $viewHandler,
        Environment $twig = null,
    ) {
        $this->giftCardRepository = $giftCardRepository;
        $this->viewHandler = $viewHandler;
        $this->twig = $twig;
    }

    public function __invoke(Request $request): Response
    {
        $giftCardBalanceCollection = GiftCardBalanceCollection::createFromGiftCards(
            $this->giftCardRepository->findEnabled(),
        );

        if (null !== $this->twig) {
            return new Response($this->twig->render('@SetonoSyliusGiftCardPlugin/Admin/giftCardBalance.html.twig', [
                'giftCardBalanceCollection' => $giftCardBalanceCollection,
            ]));
        }

        $view = View::create();
        $view
            ->setTemplate('@SetonoSyliusGiftCardPlugin/Admin/giftCardBalance.html.twig')
            ->setData([
                'giftCardBalanceCollection' => $giftCardBalanceCollection,
            ])
        ;

        return $this->viewHandler->handle($view);
    }
}
