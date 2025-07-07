<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;
use App\Entity\Product;
use App\Form\ProductForm;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
final class ProductController extends AbstractController
{
    #[Route('/products', name: 'product_index')]
    public function index(ProductRepository $repository): Response
    { 
        return $this->render(
            view: 'product/index.html.twig', 
            parameters: [
                'products' => $repository->findAll(),
            ]
        );
    }

    #[Route('/product/{id<\d+>}', name: 'product_show')]
    public function show(int $id, ProductRepository $repository): Response
    {
        $product = $repository->findOneBy(['id' => $id]);
        
        if ($product === null) {
            throw $this->createNotFoundException(
                'No product found for id ' . $id
            );
        }

        return $this->render(
            view: 'product/show.html.twig',
            parameters: [
                'product' => $product,
            ]
        );
    }

    // #[Route('/product/{id<\d+>}')]
    // public function show2(Product $product): Response
    // {
    //     return $this->render(
    //         view: 'product/show.html.twig',
    //         parameters: [
    //             'product' => $product,
    //         ]
    //     );
    // }

    #[Route('/product/new', name: 'product_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductForm::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash(
                'success', 
                'Product created successfully'
            );
            return $this->redirectToRoute(
                'product_show', 
                ['id' => $product->getId()]
            );
        }

        return $this->render(
            view: 'product/new.html.twig',
            parameters: [
                'form' => $form,
            ]
        );
    }

    #[Route('/product/{id<\d+>}/edit', name: 'product_edit')]
    public function edit(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(ProductForm::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'success', 
                'Product updated successfully'
            );
            return $this->redirectToRoute(
                'product_show', 
                ['id' => $product->getId()]
            );
        }

        return $this->render(
            view: 'product/edit.html.twig',
            parameters: [
                'form' => $form,
            ]
        );
    }

    #[Route('/product/{id<\d+>}/delete', name: 'product_delete')]
    public function delete(
        Request $request,
        Product $product,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        if ($request->isMethod('POST')) {
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash(
                'success', 
                'Product deleted successfully'
            );
            return $this->redirectToRoute('product_index');
        }

        return $this->render(
            view: 'product/delete.html.twig',
            parameters: [
                'product' => $product->getId(),
            ]
        );
    }
}
