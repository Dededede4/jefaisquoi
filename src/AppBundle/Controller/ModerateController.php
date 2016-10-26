<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Session\Session;

use AppBundle\Entity\Question;
use AppBundle\Form\QuestionType;

use AppBundle\Entity\Reponse;

use AppBundle\Entity\Vote;
use AppBundle\Form\VoteType;

class ModerateController extends Controller
{
    /**
     * @Route("/creative/moderate", name="creative_moderate_rules")
     * @Route("/login/creative/moderate", name="creative_moderate")
     */
    public function moderateAction(Request $request)
    {
        if( !$this->get('security.authorization_checker')->isGranted('ROLE_USER') ){
            return $this->render('AppBundle:moderate:rules.html.twig');
        }
        

        $em = $this
            ->getDoctrine()
            ->getManager();
        $repository = $em->getRepository('AppBundle:Question');
        $repositoryVote = $em->getRepository('AppBundle:Vote');

        if ($request->isMethod('POST')) {
            $Vote = new Vote();
            $form = $this->createForm(VoteType::class, $Vote);
            $form->handleRequest($request);

            $Vote->setChoice(($Vote->getChoice() === '1'));

            if ($form->isValid()) {
                $Question = $repository->find($Vote->question_id);

                $haveVoted = $repositoryVote->findOneBy(array('question' => $Question, 'user' => $this->getUser()));

                if (!$haveVoted && !$Question->getValided())
                {
                    $Vote->setQuestion($Question);

                    $Vote->setDate(new \DateTime())
                        ->setUser($this->getUser())
                        ->setIp($request->getClientIp())
                        ;

                    $em->persist($Vote);
                    $em->flush();

                    $result = $repositoryVote->resultVote($Question);

                    if ($result === 'accepted')
                    {
                        $Question->setValided(true);
                        $Question->setVoted(true);

                        $em->persist($Question);
                        $em->flush();

                        $repository->purgeNotValidedChild($Question->getReponse());

                        $headers = 'From: jefaisquoi <nepasrepondre@jefaisquoi.fr>' . "\r\n".
                        'Reply-To: <nepasrepondre@jefaisquoi.fr>' . "\r\n" ;
                        $message = "Bonjour !\n\nVotre situation « ". $Question->getText() ." » a été validée sur jefaisquoi.fr.\nNous vous remercions d'avoir contribué !\n\nCordialement,\nL'équipe.";

                        mail($Question->getMail(), 'Votre situation a été validée', $message, $headers);


                    }
                    if ($result === 'refused')
                    {
                        $Question->setVoted(true);
                        $Question->setSoftdeleted(true);

                        $em->persist($Question);
                        $em->flush();
                    }
                }
            }
        }

        $Question = $repository->findQuestionForVote($this->getUser());

        $votePour = new Vote();
        $votePour->question_id = $Question->getId();
        $votePour->setChoice(true);

        $voteContre = new Vote();
        $voteContre->question_id = $Question->getId();
        $voteContre->setChoice(false);

        $formPour = $this->createForm(VoteType::class, $votePour);
        $formContre = $this->createForm(VoteType::class, $voteContre);

        
        

        if (is_null($Question)){
            return $this->render('AppBundle:moderate:nomore.html.twig');
        }

        return $this->render('AppBundle:moderate:validate.html.twig', array('Question' => $Question, 'formPour' => $formPour->createView(), 'formContre' => $formContre->createView(),));
    }

    /**
     * @Route("/creative/moderate/fail", name="creative_moderate_fail")
     */
    public function failAction(Request $request){
        return $this->render('AppBundle:moderate:fail.html.twig');
    }
}
