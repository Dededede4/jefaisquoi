<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Session\Session;

use AppBundle\Entity\Question;
use AppBundle\Form\QuestionType;
use AppBundle\Entity\Reponse;

class CreativeController extends Controller
{
    /**
     * @Route("/creative/stats", name="creative_stats")
     */
    public function statsAction(Request $request)
    {
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Question')
        ;

        $limit = 5;
        $questions = $repository->findLasts($limit);

        $nbr_questions = $repository->count();
        $nbr_questions_wait = $repository->countWait();


        return $this->render('AppBundle:creative:stats.html.twig', array('questions' => $questions, 'nbr_questions' => $nbr_questions, 'nbr_questions_wait' => $nbr_questions_wait));
    }

    /**
     * @Route("/creative/contribute", name="creative_contribute")
     */
    public function contributeAction(Request $request)
    {
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Question')
        ;

        $limit = 5;
        $questions = $repository->findOlds($limit);

        return $this->render('AppBundle:creative:contribute.html.twig', array('questions' => $questions));
    }

    /**
     * @Route("/creative/contribute/{id}", name="creative_contribute_autorize")
     */
    public function allowReponseWriteAction($id, Request $request)
    {
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Question')
        ;

        $limit = 5;
        $questions = $repository->findOlds($limit);

        $find = false;
        $Question = null;
        $Reponse = null;

        foreach ($questions as $QuestionTest) {
            foreach ($QuestionTest->getReponses() as $ReponseTest) {
                if ($ReponseTest->getId() == $id)
                {
                    $Question = $QuestionTest;
                    $Reponse = $ReponseTest;
                    $find = true;
                }
            }
        }

        if ($find)
        {
            $gameManager = $this->get('app.game_manager');
            $game = $gameManager->create($Question);
            $gameManager->canWriteReponseChild($Reponse, $game['id']);

            return $this->redirect( $this->generateUrl('next', array('game_id' => $game['id'], 'test_hash' => $game['hash'], 'id' => $Reponse->getId())));
        }
        else
        {
            $request->getSession()->getFlashBag()->add('info', 'Vous recommencez le jeu car une triche a été détecté.');
            return $this->redirect( $this->generateUrl('homepage'));
        }
        
    }


    /**
     * @Route("/login/creative/moderate", name="creative_moderate")
     */
    public function moderateAction(Request $request)
    {
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Question')
        ;

        $limit = 5;
        $questions = $repository->findOlds($limit);

        return $this->render('AppBundle:creative:contribute.html.twig', array('questions' => $questions));
    }
}
