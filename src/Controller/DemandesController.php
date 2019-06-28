<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Demandes;
use App\Entity\Vote;
use App\Form\DemandesType;
use App\Repository\DemandesRepository;
use App\Repository\VoteRepository;
use DateInterval;
use DateTime;
use App\Service\VoteService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Test\Provider\DateTimeTest;
use phpDocumentor\Reflection\DocBlock\Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Tests\Extension\Core\Type\DateIntervalTypeTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\CategoriesRepository;

/**
 * @Route("/demandes")
 */
class DemandesController extends AbstractController
{
    private $categories;

    public function __construct(CategoriesRepository $categoriesRepository)
    {
        $this->categories = $categoriesRepository->findAll();
    }

    /**
     * @Route("/ouvertes", name="demandes_ouvertes", methods={"GET"})
     */
    public function ouvertes(DemandesRepository $demandesRepository, VoteService $voteService): Response
    {
        $demandes = $demandesRepository->findBy(['isOuverte' => 'True']);
        $countStatus = $voteService->countVote($demandes);
        $pourcentageVotes = $voteService->pourcentageVote($demandes);

        return $this->render('demandes/index.html.twig', [
            'demandes' => $demandes,
            'countStatus'=> $countStatus,
            'pourcentageVotes' => $pourcentageVotes,
            'categories' => $this->categories
        ]);
    }

    /**
     * @Route("/validees", name="demandes_validees", methods={"GET"})
     */
    public function validees(DemandesRepository $demandesRepository, VoteService $voteService): Response
    {
        $demandes = $demandesRepository->findBy(['isOuverte' => true, 'isValide' => true]);
        $successed = $voteService->isSuccessed($demandes, true);
        $countStatus = $voteService->countVote($demandes);
        $pourcentageVotes = $voteService->pourcentageVote($demandes);
        return $this->render('demandes/index.html.twig', [
            'demandes' => $successed,
            'countStatus'=> $countStatus,
            'pourcentageVotes' => $pourcentageVotes,
            'categories' => $this->categories
        ]);
    }

    /**
     * @Route("/rejetees", name="demandes_rejetees", methods={"GET"})
     */
    public function rejetees(DemandesRepository $demandesRepository, VoteService $voteService): Response
    {
        $demandes = $demandesRepository->findBy(['isOuverte' => true, 'isValide' => true]);
        $failed = $voteService->isSuccessed($demandes, false);
        $countStatus = $voteService->countVote($demandes);
        $pourcentageVotes = $voteService->pourcentageVote($demandes);
        return $this->render('demandes/index.html.twig', [
            'demandes' => $failed,
            'countStatus'=> $countStatus,
            'pourcentageVotes' => $pourcentageVotes,
            'categories' => $this->categories
        ]);
    }

    /**
     * @Route("/terminees", name="demandes_terminees", methods={"GET"})
     */
    public function terminees(DemandesRepository $demandesRepository, VoteService $voteService): Response
    {
        $demandes = $demandesRepository->findBy(['isOuverte' => false, 'isValide' => true]);
        $countStatus = $voteService->countVote($demandes);
        $pourcentageVotes = $voteService->pourcentageVote($demandes);

        return $this->render('demandes/index.html.twig', [
            'demandes' => $demandes,
            'countStatus'=> $countStatus,
            'pourcentageVotes' => $pourcentageVotes,
            'categories' => $this->categories
        ]);
    }
// TODO : La Vue annulee
    /**
     * @Route("/annulees", name="demandes_annulees", methods={"GET"})
     */
    public function annulees(DemandesRepository $demandesRepository): Response
    {
        return $this->render('demandes/index.html.twig', [
            'demandes' => $demandesRepository->findBy(['isValide' => false]),
            'categories' => $this->categories
        ]);
    }





    /**
     * @Route("/new", name="demandes_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $demande = new Demandes();
        $form = $this->createForm(DemandesType::class, $demande);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demande->setCreatedAt(new DateTime());
            $now = new DateTime();
            $demande->setDeadline($now->add(new DateInterval('P1M')));
            $demande->setIsOuverte(true);
            $demande->setIsValide(true);

            $demande->setCreateur($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($demande);
            $entityManager->flush();

            return $this->redirectToRoute('demandes_index');
        }

        return $this->render('demandes/new.html.twig', [
            'demande' => $demande,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="demandes_show", methods={"GET"})
     */
    public function show(Demandes $demande): Response
    {
        return $this->render('home/show.html.twig', [
            'demande' => $demande,

        ]);
    }

    /**
     * @Route("/{id}/edit", name="demandes_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Demandes $demande): Response
    {
        $form = $this->createForm(DemandesType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('demandes_index', [
                'id' => $demande->getId(),
            ]);
        }

        return $this->render('demandes/edit.html.twig', [
            'demande' => $demande,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="demandes_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Demandes $demande): Response
    {
        if ($this->isCsrfTokenValid('delete'.$demande->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($demande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('demandes_index');
    }

	/**
	 * @param DemandesRepository $demandesRepository
	 * @Route("/json/", name="demande_active", methods={"GET"})
	 */
    public function demandesOuverteJson(DemandesRepository $demandesRepository, SerializerInterface $serializer):Response
	{
		$demandeOuverte = $demandesRepository->findBy(['isOuverte' => true]);

		$demandeOuverteJson = $serializer->serialize($demandeOuverte, 'json', [
			'attributes' => [
                'id',
				'titre',
				'contenu',
				'sommaire',
				'budget',
				'categorie' => ['categories' => 'nom'],
				'createdAt',
				'deadline',
				'createur' => ['citoyen' => 'id', 'nom', 'prenom'],
				'nombreVotes',
				'voteurs' => ['citoyen' => 'id', 'nom', 'prenom'],
				'votes' => ['vote' => 'etat']
			]]);

		return new Response($demandeOuverteJson, 200, ['content-type'=> 'application/json']);
	}
}
