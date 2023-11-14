<?php

declare(strict_types=1);

namespace Setono\SyliusGiftCardPlugin\Controller\Action;

use const FILTER_SANITIZE_URL;
use function filter_var;
use Setono\SyliusGiftCardPlugin\EmailManager\GiftCardEmailManagerInterface;
use Setono\SyliusGiftCardPlugin\Model\GiftCardInterface;
use Setono\SyliusGiftCardPlugin\Model\OrderInterface;
use Setono\SyliusGiftCardPlugin\Repository\GiftCardRepositoryInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ResendGiftCardEmailAction
{
    private GiftCardEmailManagerInterface $giftCardEmailManager;

    private GiftCardRepositoryInterface $giftCardRepository;

    private UrlGeneratorInterface $router;

    private RequestStack $requestStack;

    public function __construct(
        GiftCardEmailManagerInterface $giftCardEmailManager,
        GiftCardRepositoryInterface $giftCardRepository,
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
    ) {
        $this->giftCardEmailManager = $giftCardEmailManager;
        $this->giftCardRepository = $giftCardRepository;
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    public function __invoke(Request $request, int $id): Response
    {
        $giftCard = $this->giftCardRepository->find($id);
        if (!$giftCard instanceof GiftCardInterface) {
            $session = $this->requestStack->getSession();
            if ($session instanceof FlashBagAwareSessionInterface) {
                $session->getFlashBag()->add('error', [
                    'message' => 'setono_sylius_gift_card.gift_card.not_found',
                    'parameters' => ['%id%' => $id],
                ]);
            }

            return new RedirectResponse($this->getRedirectRoute($request));
        }
        $session = $this->requestStack->getSession();

        if ($giftCard->getOrder() instanceof OrderInterface) {
            $this->giftCardEmailManager->sendEmailWithGiftCardsFromOrder($giftCard->getOrder(), [$giftCard]);
            if ($session instanceof FlashBagAwareSessionInterface) {
                $session->getFlashBag()->add('success', [
                    'message' => 'setono_sylius_gift_card.gift_card.resent',
                    'parameters' => ['%id%' => $id],
                ]);
            }
        } elseif ($giftCard->getCustomer() instanceof CustomerInterface) {
            $this->giftCardEmailManager->sendEmailToCustomerWithGiftCard($giftCard->getCustomer(), $giftCard);
            if ($session instanceof FlashBagAwareSessionInterface) {
                $session->getFlashBag()->add('success', [
                    'message' => 'setono_sylius_gift_card.gift_card.resent',
                    'parameters' => ['%id%' => $id],
                ]);
            }
        } else {
            if ($session instanceof FlashBagAwareSessionInterface) {
                $session->getFlashBag()->add('error', [
                    'message' => 'setono_sylius_gift_card.gift_card.impossible_to_resend_email',
                    'parameters' => ['%id%' => $id],
                ]);
            }
        }

        return new RedirectResponse($this->getRedirectRoute($request));
    }

    private function getRedirectRoute(Request $request): string
    {
        if ($request->headers->has('referer')) {
            $filtered = filter_var($request->headers->get('referer'), FILTER_SANITIZE_URL);

            if (false === $filtered) {
                return $this->router->generate('setono_sylius_gift_card_admin_gift_card_index');
            }
        }

        return $this->router->generate('setono_sylius_gift_card_admin_gift_card_index');
    }
}
