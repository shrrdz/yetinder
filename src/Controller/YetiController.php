<?php

namespace App\Controller;

use App\Entity\Yeti;
use App\Entity\User;
use App\Form\YetiType;
use App\Repository\YetiRepository;
use DateTime;
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
    public function index(YetiRepository $yetiRepository): Response
    {
        // fetch top ten yetis from the database based on their rating
        $topRatedYetis = $yetiRepository->findTopRatedYetis();

        return $this->render('app/index.html.twig', [
            'top_rated_yetis' => $topRatedYetis
        ]);
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

    #[Route(path: '/rate-yeti', name: 'rate-yeti')]
    public function randomYeti(YetiRepository $yetiRepository): Response
    {
        $yetis = $yetiRepository->findAll();

        // there are no yetis in the database
        if (!$yetis)
        {
            return $this->render('app/rate-yeti.html.twig', [
                'yeti' => null,
            ]);
        }

        // pick a random yeti
        $randomYeti = $yetis[array_rand($yetis)];

        return $this->render('app/rate-yeti.html.twig', [
            'yeti' => $randomYeti,
        ]);
    }

    #[Route(path: '/upvote-yeti/{id}', name: 'upvote-yeti', methods: ['POST'])]
    public function upvoteYeti(YetiRepository $yetiRepository, $id): Response
    {
        $yeti = $yetiRepository->find($id);

        $now = new DateTime();
        $date = date('Y/m/d H:i:s', $now->getTimestamp());

        $yeti->setRating($yeti->getRating() + 1);
        $yeti->addRatingToHistory([$date => ($yeti->getRating())]);

        $this->em->persist($yeti);
        $this->em->flush();

        return $this->redirectToRoute('rate-yeti');
    }

    #[Route(path: '/downvote-yeti/{id}', name: 'downvote-yeti', methods: ['POST'])]
    public function downvoteYeti(YetiRepository $yetiRepository, $id): Response
    {
        $yeti = $yetiRepository->find($id);

        $now = new DateTime();
        $date = date('Y/m/d H:i:s', $now->getTimestamp());

        $yeti->setRating($yeti->getRating() - 1);
        $yeti->addRatingToHistory([$date => ($yeti->getRating())]);

        $this->em->persist($yeti);
        $this->em->flush();

        return $this->redirectToRoute('rate-yeti');
    }

    #[Route(path: '/rating-history/{id}', name: 'rating-history')]
    public function ratingHistory(YetiRepository $yetiRepository, $id)
    {
        $yeti = $yetiRepository->find($id);

        return $this->render('app/rating-history.html.twig', [
            'yeti' => $yeti
        ]);
    }
}
