<?php

namespace App\Controller;

use App\Entity\Yeti;
use App\Form\YetiType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class YetiController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route(path: '/', name: 'index')]
    public function index(): Response
    {
        return $this->render('app/index.html.twig');
    }

    // add a Yeti to the database
    #[Route(path: '/add-yeti', name: 'add-yeti')]
    public function addYeti(Request $request)
    {
        $yeti = new Yeti();

        $form = $this->createForm(YetiType::class, $yeti);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->em->persist($yeti);
            $this->em->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render('app/add-yeti.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
