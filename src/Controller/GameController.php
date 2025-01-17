<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Round;
use App\Repository\CardRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
	/**
	 * @Route("/new-game", name="new_game")
	 */
	public function newGame(
		UserRepository $userRepository
	): Response {
		$users = $userRepository->findAll();

		return $this->render('game/index.html.twig', [
			'users' => $users
		]);
	}
	/**
	 * @Route("/create-game", name="create_game")
	 */
	public function createGame(
		Request $request,
		EntityManagerInterface $entityManager,
		UserRepository $userRepository,
		CardRepository $cardRepository
	): Response {
		$user1 = $this->getUser();
		$user2 = $userRepository->find($request->request->get('user2'));

		if ($user1 !== $user2) {
			$game = new Game();
			$game->setUser1($user1);
			$game->setUser2($user2);
			$game->setCreated(new \DateTime('now'));

			$entityManager->persist($game);

			$set = new Round();
			$set->setGame($game);
			$set->setCreated(new \DateTime('now'));
			$set->setSetNumber(1);

			$cards = $cardRepository->findAll();
			$tCards = [];
			foreach ($cards as $card) {
				$tCards[$card->getId()] = $card;
			}
			shuffle($tCards);
			$carte = array_pop($tCards);
			$set->setRemovedCard($carte->getId());

			$tMainJ1 = [];
			$tMainJ2 = [];
			for ($i = 0; $i < 6; $i++) {
				//on distribue 6 cartes aux deux joueurs
				$carte = array_pop($tCards);
				$tMainJ1[] = $carte->getId();
				$carte = array_pop($tCards);
				$tMainJ2[] = $carte->getId();
			}
			$set->setUser1HandCards($tMainJ1);
			$set->setUser2HandCards($tMainJ2);

			$tPioche = [];

			foreach ($tCards as $card) {
				$carte = array_pop($tCards);
				$tPioche[] = $carte->getId();
			}

			$set->setPioche($tPioche);

			$set->setUser1Action([
				'SECRET' => false,
				'DEPOT' => false,
				'OFFRE' => false,
				'ECHANGE' => false
			]);

			$set->setUser2Action([
				'SECRET' => false,
				'DEPOT' => false,
				'OFFRE' => false,
				'ECHANGE' => false
			]);

			$set->setBoard([
				'EMPL1' => ['N'],
				'EMPL2' => ['N'],
				'EMPL3' => ['N'],
				'EMPL4' => ['N'],
				'EMPL5' => ['N'],
				'EMPL6' => ['N'],
				'EMPL7' => ['N']
			]);
			$entityManager->persist($set);
			$entityManager->flush();

			return $this->redirectToRoute('show_game', [
				'game' => $game->getId()
			]);
		} else {
			return $this->redirectToRoute('new_game');
		}
	}

	/**
	 * @Route("/show-game/{game}", name="show_game")
	 */
	public function showGame(
		Game $game
	): Response {

		return $this->render('game/show_game.html.twig', [
			'game' => $game
		]);
	}

	/**
	 * @Route("/fin-tour/{game}", name="fin_tour")
	 */
	public function finTour(
		Game $game,
		EntityManagerInterface $entityManager
	): Response {
		if ($game->getQuiJoue()==1){
			$game->setQuiJoue(2);
		}else{
			$game->setQuiJoue(1);
		}

		$entityManager->persist($game);
		$entityManager->flush();

		return $this->json(true);
	}

	/**
	 * @Route("/get-tout-game/{game}", name="get_tour")
	 */
	public function getTour(
		Game $game,
		EntityManagerInterface $entityManager
	): Response {
		if ($this->getUser()->getId() === $game->getUser1()->getId() && $game->getQuiJoue() === 1) {
			return $this->json(true);
		}

		if ($this->getUser()->getId() === $game->getUser2()->getId() && $game->getQuiJoue() === 2) {
			return $this->json(true);
		}

		return $this->json( false);
	}


	/**
	 * @param Game $game
	 * @route("/refresh/{game}", name="refresh_plateau_game")
	 */
	public function refreshPlateauGame(CardRepository $cardRepository, Game $game)
	{
		$cards = $cardRepository->findAll();
		$tCards = [];
		foreach ($cards as $card) {
			$tCards[$card->getId()] = $card;
		}

		if ($this->getUser()->getId() === $game->getUser1()->getId()) {
			$moi['handCards'] = $game->getRounds()[0]->getUser1HandCards();
			$moi['actions'] = $game->getRounds()[0]->getUser1Action();
			$moi['board'] = $game->getRounds()[0]->getUser1BoardCards();
			$adversaire['handCards'] = $game->getRounds()[0]->getUser2HandCards();
			$adversaire['actions'] = $game->getRounds()[0]->getUser2Action();
			$adversaire['board'] = $game->getRounds()[0]->getUser2BoardCards();
			if ($game->getQuiJoue() === 1) {
				$tour = 'moi';
			} else {
				$tour = 'adversaire';
			}
		} elseif ($this->getUser()->getId() === $game->getUser2()->getId()) {
			$moi['handCards'] = $game->getRounds()[0]->getUser2HandCards();
			$moi['actions'] = $game->getRounds()[0]->getUser2Action();
			$moi['board'] = $game->getRounds()[0]->getUser2BoardCards();
			$adversaire['handCards'] = $game->getRounds()[0]->getUser1HandCards();
			$adversaire['actions'] = $game->getRounds()[0]->getUser1Action();
			$adversaire['board'] = $game->getRounds()[0]->getUser1BoardCards();
			if ($game->getQuiJoue() === 2) {
				$tour = 'moi';
			} else {
				$tour = 'adversaire';
			}
		} else {
			//redirection... je ne suis pas l'un des deux joueurs
		}

		return $this->render('game/plateau_game.html.twig', [
			'game' => $game,
			'set' => $game->getRounds()[0],
			'cards' => $tCards,
			'moi' => $moi,
			'tour' => $tour,
			'adversaire' => $adversaire
		]);
	}

	/**
	 * @Route("/action-game/{game}", name="action_game")
	 */
	public function actionGame(
		EntityManagerInterface $entityManager,
		Request $request, Game $game){


		$action = $request->request->get('action');
		$user = $this->getUser();
		$round = $game->getRounds()[0]; //a gérer selon le round en cours

		if ($game->getUser1()->getId() === $user->getId())
		{
			$joueur = 1;
		} elseif ($game->getUser2()->getId() === $user->getId()) {
			$joueur = 2;
		} else {
			/// On a un problème... On pourrait rediriger vers une page d'erreur.
		}

		switch ($action) {
			case 'secret':
				$carte = $request->request->get('carte');
				if ($joueur === 1) {
					$actions = $round->getUser1Action(); //un tableau...
					$actions['SECRET'] = [$carte]; //je sauvegarde la carte cachée dans mes actions
					$round->setUser1Action($actions); //je mets à jour le tableau
					$main = $round->getUser1HandCards();
					$indexCarte = array_search($carte, $main); //je récupère l'index de la carte a supprimer dans ma main
					unset($main[$indexCarte]); //je supprime la carte de ma main
					$round->setUser1HandCards($main);
				}
				if ($joueur === 2) {
					$actions = $round->getUser2Action(); //un tableau...
					$actions['SECRET'] = [$carte]; //je sauvegarde la carte cachée dans mes actions
					$round->setUser2Action($actions); //je mets à jour le tableau
					$main = $round->getUser2HandCards();
					$indexCarte = array_search($carte, $main); //je récupère l'index de la carte a supprimer dans ma main
					unset($main[$indexCarte]); //je supprime la carte de ma main
					$round->setUser2HandCards($main);
				}
				break;

			case 'depot':
				$carte = $request->request->get('carte');
				$carte2 = $request->request->get('carte2');

				if ($joueur === 1) {
					$actions = $round->getUser1Action(); //un tableau...
					$actions['DEPOT'] = [$carte,$carte2]; //je sauvegarde la carte cachée dans mes actions
					$round->setUser1Action($actions); //je mets à jour le tableau
					$main = $round->getUser1HandCards();

					$indexCarte = array_search($carte, $main); //je récupère l'index de la carte a supprimer dans ma main
					unset($main[$indexCarte]); //je supprime la carte de ma main
					$indexCarte = array_search($carte2, $main); //je récupère l'index de la carte a supprimer dans ma main
					unset($main[$indexCarte]); //je supprime la carte de ma main

					$round->setUser1HandCards($main);
				}
				if ($joueur === 2) {
					$actions = $round->getUser2Action(); //un tableau...
					$actions['DEPOT'] = [$carte,$carte2]; //je sauvegarde la carte cachée dans mes actions
					$round->setUser2Action($actions); //je mets à jour le tableau
					$main = $round->getUser2HandCards();

					$indexCarte = array_search($carte, $main); //je récupère l'index de la carte a supprimer dans ma main
					unset($main[$indexCarte]); //je supprime la carte de ma main
					$indexCarte = array_search($carte2, $main); //je récupère l'index de la carte a supprimer dans ma main
					unset($main[$indexCarte]); //je supprime la carte de ma main

					$round->setUser2HandCards($main);
				}
				break;

			case 'offre':
				$carte1 = $request->request->get('carte1');
				$carte2 = $request->request->get('carte2');
				$carte3 = $request->request->get('carte3');
				if ($joueur === 1) {
					$actions = $round->getUser1Action(); //un tableau...
					$actions['OFFRE'] = [$carte1,$carte2,$carte3]; //je sauvegarde la carte cachée dans mes actions
					$round->setUser1Action($actions); //je mets à jour le tableau
					$main = $round->getUser1HandCards();

					$indexCarte = array_search($carte1, $main); //je récupère l'index de la carte a supprimer dans ma main
					unset($main[$indexCarte]); //je supprime la carte de ma main
					$indexCarte = array_search($carte2, $main); //je récupère l'index de la carte a supprimer dans ma main
					unset($main[$indexCarte]); //je supprime la carte de ma main
					$indexCarte = array_search($carte3, $main); //je récupère l'index de la carte a supprimer dans ma main
					unset($main[$indexCarte]); //je supprime la carte de ma main

					$round->setUser1HandCards($main);
				}
				if ($joueur === 2) {
					$actions = $round->getUser2Action(); //un tableau...
					$actions['OFFRE'] = [$carte1,$carte2,$carte3]; //je sauvegarde la carte cachée dans mes actions
					$round->setUser2Action($actions); //je mets à jour le tableau
					$main = $round->getUser2HandCards();

					$indexCarte = array_search($carte1, $main); //je récupère l'index de la carte a supprimer dans ma main
					unset($main[$indexCarte]); //je supprime la carte de ma main
					$indexCarte = array_search($carte2, $main); //je récupère l'index de la carte a supprimer dans ma main
					unset($main[$indexCarte]); //je supprime la carte de ma main
					$indexCarte = array_search($carte3, $main); //je récupère l'index de la carte a supprimer dans ma main
					unset($main[$indexCarte]); //je supprime la carte de ma main

					$round->setUser2HandCards($main);
				}
				break;
				case 'offreValid':
					$carte = $request->request->get('carte');
					if ($joueur === 1) {
						$actions = $round->getUser2Action();
						$board1 = $round->getUser1BoardCards();
						$board2 = $round->getUser2BoardCards();
						$carteChoisie = array_search($carte, $actions['OFFRE']);
						array_splice($actions['OFFRE'], $carteChoisie, 1);
						$board1[] = $carte;
						$board2[] = $actions['OFFRE'][0];
						$board2[] = $actions['OFFRE'][1];
						$actions['OFFRE'] = 'done';
						$round->setUser2Action($actions);
						$round->setUser1BoardCards($board1);
						$round->setUser2BoardCards($board2);
						$round->setUser2Action($actions);
						/*$game->setQuiJoue(1);*/
					}
					if ($joueur === 2) {
						$actions = $round->getUser1Action();
						$board1 = $round->getUser1BoardCards();
						$board2 = $round->getUser2BoardCards();
						$carteChoisie = array_search($carte, $actions['OFFRE']);
						array_splice($actions['OFFRE'], $carteChoisie, 1);
						$board2[] = $carte;
						$board1[] = $actions['OFFRE'][0];
						$board1[] = $actions['OFFRE'][1];
						$actions['OFFRE'] = 'done';
						$round->setUser1Action($actions);
						$round->setUser1BoardCards($board1);
						$round->setUser2BoardCards($board2);
						$round->setUser1Action($actions);
						/*$game->setQuiJoue(2);*/
					}
					break;
			case 'echange':
				$carte1 = $request->request->get('carte1');
				$carte2 = $request->request->get('carte2');
				$carte3 = $request->request->get('carte3');
				$carte4 = $request->request->get('carte4');
				if ($joueur === 1) {
					$actions = $round->getUser1Action(); //un tableau...
					$actions['ECHANGE'] = [$carte1,$carte2,$carte3,$carte4]; //je sauvegarde la carte cachée dans mes actions
					$round->setUser1Action($actions); //je mets à jour le tableau
					$main = $round->getUser1HandCards();

					$indexCarte = array_search($carte1, $main);
					unset($main[$indexCarte]);
					$indexCarte = array_search($carte2, $main);
					unset($main[$indexCarte]);
					$indexCarte = array_search($carte3, $main);
					unset($main[$indexCarte]);
					$indexCarte = array_search($carte4, $main);
					unset($main[$indexCarte]);

					$round->setUser1HandCards($main);
				}
				if ($joueur === 2) {
					$actions = $round->getUser2Action();
					$actions['ECHANGE'] = [$carte1,$carte2,$carte3,$carte4];
					$round->setUser2Action($actions);
					$main = $round->getUser2HandCards();

					$indexCarte = array_search($carte1, $main);
					unset($main[$indexCarte]);
					$indexCarte = array_search($carte2, $main);
					unset($main[$indexCarte]);
					$indexCarte = array_search($carte3, $main);
					unset($main[$indexCarte]);
					$indexCarte = array_search($carte4, $main);
					unset($main[$indexCarte]);

					$round->setUser2HandCards($main);
				}
				break;

			case 'echangeGroup':
				$carte1 = $request->request->get('carte1');
				$carte2 = $request->request->get('carte2');
				$carteP1 = [];

				if ($joueur === 1) {
					$actions = $round->getUser1Action();
					$echange = $actions['ECHANGE'];

					$indexCarte = array_search($carte1, $echange);
					array_splice($echange, $indexCarte, 1);
					$indexCarte = array_search($carte2, $echange);
					array_splice($echange, $indexCarte, 1);

					array_push($carteP1, $carte1, $carte2);
					$actions['ECHANGE'] = [];
					$actions['ECHANGE']['group1'] = $carteP1;
					$actions['ECHANGE']['group2'] = $echange;

					$round->setUser1Action($actions);
				}
				if ($joueur === 2) {
					$actions = $round->getUser2Action();
					$echange = $actions['ECHANGE'];

					$indexCarte = array_search($carte1, $echange);
					array_splice($echange, $indexCarte, 1);
					$indexCarte = array_search($carte2, $echange);
					array_splice($echange, $indexCarte, 1);

					array_push($carteP1, $carte1, $carte2);
					$actions['ECHANGE'] = [];
					$actions['ECHANGE']['group1'] = $carteP1;
					$actions['ECHANGE']['group2'] = $echange;

					$round->setUser2Action($actions);
				}
				break;
			case 'echangeValid':
				$groupChoisi = $request->request->get('group');
				if ($joueur === 1) {
					$actions = $round->getUser2Action();
					$board1 = $round->getUser1BoardCards();
					$board2 = $round->getUser2BoardCards();
					if ($groupChoisi == 'group1'){
						$board1[] = $actions['ECHANGE']['group1'][0];
						$board1[] = $actions['ECHANGE']['group1'][1];

						$board2[] = $actions['ECHANGE']['group2'][0];
						$board2[] = $actions['ECHANGE']['group2'][1];
					}else{
						$board2[] = $actions['ECHANGE']['group1'][0];
						$board2[] = $actions['ECHANGE']['group1'][1];

						$board1[] = $actions['ECHANGE']['group2'][0];
						$board1[] = $actions['ECHANGE']['group2'][1];
					}
					$actions['ECHANGE'] = 'done';
					$round->setUser2Action($actions);
					$round->setUser1BoardCards($board1);
					$round->setUser2BoardCards($board2);
				}
				if ($joueur === 2) {
					$actions = $round->getUser1Action();
					$board1 = $round->getUser1BoardCards();
					$board2 = $round->getUser2BoardCards();
					if ($groupChoisi == 'group1'){
						$board2[] = $actions['ECHANGE']['group1'][0];
						$board2[] = $actions['ECHANGE']['group1'][1];

						$board1[] = $actions['ECHANGE']['group2'][0];
						$board1[] = $actions['ECHANGE']['group2'][1];
					}else{
						$board1[] = $actions['ECHANGE']['group1'][0];
						$board1[] = $actions['ECHANGE']['group1'][1];

						$board2[] = $actions['ECHANGE']['group2'][0];
						$board2[] = $actions['ECHANGE']['group2'][1];
					}
					$actions['ECHANGE'] = 'done';
					$round->setUser1Action($actions);
					$round->setUser1BoardCards($board1);
					$round->setUser2BoardCards($board2);
				}
				break;
		}

		$entityManager->flush();

		return $this->json(true);
	}

	/**
	 * @Route("/pioche/{game}", name="pioche")
	 */
	public function Pioche(
		Game $game,
		EntityManagerInterface $entityManager,
		CardRepository $cardRepository
	): Response
	{
		if ($this->getUser()->getId() === $game->getUser1()->getId()) {
			if ($game->getSets()[0]->getUser1Pioche() == 0) {
				$game->getSets()[0]->setUser1Pioche(1);
				$hands = $game->getSets()[0]->getUser1HandCards();
				array_push($hands, $game->getSets()[0]->getPioche()[(sizeof($game->getSets()[0]->getPioche()))-1]);
				$game->getSets()[0]->setUser1HandCards($hands);
				$carte=$game->getSets()[0]->getPioche()[(sizeof($game->getSets()[0]->getPioche()))-1];
				$pioche = $game->getSets()[0]->getPioche();
				unset($pioche[(sizeof($game->getSets()[0]->getPioche()))-1]);
				$game->getSets()[0]->setPioche($pioche);
				$entityManager->persist($game->getSets()[0]);
				$entityManager->flush();
				$response=$cardRepository->findBy(array('id'=>$carte));
				return $this->json([$response[0]->getId(),$response[0]->getPicture()]);
			}
		} elseif ($this->getUser()->getId() === $game->getUser2()->getId()) {
			if ($game->getSets()[0]->getUser2Pioche() == 0) {
				$game->getSets()[0]->setUser2Pioche(1);
				$hands = $game->getSets()[0]->getUser2HandCards();
				array_push($hands, $game->getSets()[0]->getPioche()[(sizeof($game->getSets()[0]->getPioche()))-1]);
				$game->getSets()[0]->setUser2HandCards($hands);
				$carte=$game->getSets()[0]->getPioche()[(sizeof($game->getSets()[0]->getPioche()))-1];
				$pioche = $game->getSets()[0]->getPioche();
				unset($pioche[(sizeof($game->getSets()[0]->getPioche()))-1]);
				$game->getSets()[0]->setPioche($pioche);
				$entityManager->persist($game->getSets()[0]);
				$entityManager->flush();
				$response=$cardRepository->findBy(array('id'=>$carte));
				return $this->json([$response[0]->getId(),$response[0]->getPicture()]);
			}
		}
		return $this->json(true);
	}

	/**
	 * @Route("/reset-pioche/{game}", name="reset_pioche")
	 */
	public function resetPioche(
		Game $game,
		EntityManagerInterface $entityManager
	): Response {
		if ($this->getUser()->getId() === $game->getUser1()->getId() && $game->getQuiJoue() === 1) {
			if ($game->getSets()[0]->getUser1Pioche() == 1) {
				$game->getSets()[0]->setUser1Pioche(0);
				$entityManager->persist($game->getSets()[0]);
				$entityManager->flush();
			}
		}

		if ($this->getUser()->getId() === $game->getUser2()->getId() && $game->getQuiJoue() === 2) {
			if ($game->getSets()[0]->getUser2Pioche() == 1) {
				$game->getSets()[0]->setUser2Pioche(0);
				$entityManager->persist($game->getSets()[0]);
				$entityManager->flush();
			}
		}

		return $this->json(true);
	}


}
