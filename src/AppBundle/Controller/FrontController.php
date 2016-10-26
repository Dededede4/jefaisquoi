<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Session\Session;

use AppBundle\Entity\Question;
use AppBundle\Form\QuestionType;
use AppBundle\Entity\Reponse;

class FrontController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Question')
        ;

        $Question = $repository->findFirst();

        $gameManager = $this->get('app.game_manager');
        $game = $gameManager->create($Question);

        return $this->render('AppBundle::home.html.twig',
            array(
                    'Question' => $Question,
                    'game_id' => $game['id'],
                    'valid_hash' => $game['hash'],
                    'score' => '0',
                    'accueil' => true
                )
        );
    }

    /**
     * @Route("/next/{game_id}/{test_hash}/{id}", name="next")
     */
    public function nextAction($game_id, $test_hash, Reponse $Reponse, Request $request)
    {
        $errors = false;

        $gameManager = $this->get('app.game_manager');
        $canPlay = $gameManager->canSeeReponseChild($Reponse, $game_id, $test_hash);

        if (!$canPlay)
        {
            $request->getSession()->getFlashBag()->add('info', 'Vous recommencez le jeu car une triche a été détecté.');
            return $this->redirect( $this->generateUrl('homepage'));
        }

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Question')
        ;

        $Question = $repository->getQuestionFromParentReponse($Reponse);

        if (is_null($Question) && !$gameManager->canWriteElsewhere($game_id, $Reponse))
        {
            $gameManager->allowWriteReponseChild($Reponse, $game_id);
        }

        $canWrite = $gameManager->canWriteReponseChild($Reponse, $game_id);

        if (is_null($Question))
        {
            
            if (!$canWrite)
            {
                $request->getSession()->getFlashBag()->add('info', 'Vous recommencez le jeu car une triche a été détecté.');
                return $this->redirect( $this->generateUrl('homepage'));
            }

            

            $parent_Reponse = $Reponse;

            $Question = new Question();

            $form = $this->createForm(QuestionType::class, $Question);

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);

                

                if ($Question->getDead()) {
                    $Question->setReponses(new \Doctrine\Common\Collections\ArrayCollection());
                }

                if ($form->isValid()) {
                    
                    $reponses = $Question->getReponses();
                    foreach ($reponses as $Reponse) {
                        if (empty($Reponse->getText()))
                        {
                            $Question->removeReponse($Reponse);
                        }
                        else{
                            $Reponse->setQuestion($Question); // …
                        }
                    }

                    $Question
                            ->setIp($request->getClientIp())
                            ->setDate(new \Datetime())
                            ->setValided(false)
                            ->setReponse($parent_Reponse)
                            ->setVoted(false)
                            ->setSoftdeleted(false)
                            ;
                    $this->get('session')->set('name', $Question->getUsername());
                    $this->get('session')->set('mail', $Question->getMail());

                    // On récupère l'EntityManager
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($Question);
                    $em->flush();
                    $request->getSession()->getFlashBag()->add('info', 'Votre réponse est enregistrée et en attente de validation !');

                    if ($canWrite['score'] === 0)
                    {
                        return $this->redirect( $this->generateUrl('creative_contribute'));
                    }
                    else{
                        return $this->redirect( $this->generateUrl('homepage'));
                    }
                }
                else{
                    $errors = array();
                    foreach ($form->getErrors() as $error) {
                        $errors[] = $error->getMessage();
                    }
                }
            }
            else{
                $name = $this->get('session')->get('name');
                $mail = $this->get('session')->get('mail');

                $Question->setUsername($name);
                $Question->setMail($mail);
            
                $Reponse1 = new Reponse();
                $Reponse2 = new Reponse();
                $Reponse3 = new Reponse();
                $Reponse4 = new Reponse();

                $Question
                    ->addReponse($Reponse1)->addReponse($Reponse2)->addReponse($Reponse3)->addReponse($Reponse4);

                $form = $this->createForm(QuestionType::class, $Question);
            }

            return $this->render('AppBundle::create.html.twig', array(
                'form' => $form->createView(),
                'score' => $canWrite['score'],
                'errors' => $errors,
                'parent_Reponse' => $parent_Reponse
            ));
        }
        else
        {
            if ($request->isMethod('POST')) {
                $request->getSession()->getFlashBag()->add('info', 'Une nouvelle situation a été validée pendant que vous écriviez.');
                return $this->redirect( $this->generateUrl('homepage'));
            }

            if ($canWrite){
                $request->getSession()->getFlashBag()->add('info', 'Vous recommencez le jeu car une triche a été détecté.');
                return $this->redirect( $this->generateUrl('homepage'));
            }
            
            $game = $gameManager->defineQuestion($Question, $game_id);
            return $this->render('AppBundle::home.html.twig',
                        array(  'Question' => $Question,
                                'game_id' => $game['game_id'],
                                'valid_hash' => $game['hash'],
                                'score' => $game['score'],
                                'accueil' => false));
            
        }

    }
}
