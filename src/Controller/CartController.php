<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\CartItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class CartController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function getCart(#[CurrentUser] ?User $user): JsonResponse
    {
        return $this->json($user?->getCartItems(), 200, [], ['groups' => 'cart:read']);
    }

    #[Route('', methods: ['POST'])]
    public function saveCart(Request $request, #[CurrentUser] ?User $user, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user->clearCartItems(); // logique pour effacer l'ancien panier

        foreach ($data as $itemData) {
            $item = new CartItem();
            $item->setProductName($itemData['name']);
            $item->setQuantity($itemData['quantity']);
            $item->setPrice($itemData['price']);
            $item->setUser($user);
            $em->persist($item);
        }

        $em->flush();
        return $this->json(['status' => 'saved'], 200);
    }

    #[Route('', methods: ['DELETE'])]
    public function clearCart(#[CurrentUser] ?User $user, EntityManagerInterface $em): JsonResponse
    {
        $user->clearCartItems();
        $em->flush();
        return $this->json(['status' => 'cleared'], 200);
    }
}