<?php

namespace App\Controller;

use Exception;
use App\Entity\Post;
use App\Entity\Product;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/blog")
 */
class BlogController extends AbstractController
{
    /**
     * @Route("/", name="blog", methods={"GET","POST"})
     */
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('blog/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    /**
    * @Route("/new", name="blog_new", methods={"GET","POST"})
    */
    public function new(Request $request): Response
    {
        $post = new Product();
        $form = $this->createForm(ProductType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('blog_index');
        }

        return $this->render('blog/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="blog_show", methods={"GET"})
     */
    public function show(Post $post): Response
    {
        return $this->render('blog/show.html.twig', [
            'post' => $post,
        ]);
    }

    /**
    * @Route("/{id}/edit", name="blog_edit", methods={"GET","POST"})
    */
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(ProductType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var File $imageFile
             */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $slugger = new AsciiSlugger();
                $safeFilename = $slugger->slug($post->getTitle()) . "." . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('blog_images_directory'), $safeFilename);
                } catch (Exception $e) {
                    die($e->getMessage());
                }
                $post->setImage($safeFilename);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('blog_index');
        }

        return $this->render('blog/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="blog_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('blog_index');
    }
}