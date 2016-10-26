<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Question\Question as SFQuestion;

use AppBundle\Entity\Question;
use AppBundle\Entity\Reponse;


class CreateFirstQuestionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
	    $this
	        // the name of the command (the part after "bin/console")
	        ->setName('app:create-first-question')

	        // the short description shown while running "php bin/console list"
	        ->setDescription('Creates first question.')

	        // the full command description shown when running the command with
	        // the "--help" option
	        ->setHelp("This command allows you to create first question")
	    ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $repository = $doctrine->getRepository('AppBundle:Question');
        if($repository->count() > 0)
        {
        	$output->writeln('You have already configured the first question.');
        	return;
        }

    	$helper = $this->getHelper('question');

    	$default = 'Vous êtes devant une maison.';
        $question = new SFQuestion('Please enter first question ['.$default.']: ', $default);
        $QuestionText = $bundle = $helper->ask($input, $output, $question);

        $reponsesText = array();

        $default = 'Je rentre en utilisant la porte d\'entrée.';
        $question = new SFQuestion('Please enter first response ['.$default.']: ', $default);
        
        array_push($reponsesText, $bundle = $helper->ask($input, $output, $question));

        $default = 'Je rentre par le garage.';
        $question = new SFQuestion('Please enter second response ['.$default.']: ', $default);

        array_push($reponsesText, $bundle = $helper->ask($input, $output, $question));

        $default = 'Je rentre par le jardin.';
        $question = new SFQuestion('Please enter third response ['.$default.']: ', $default);
        
        array_push($reponsesText, $bundle = $helper->ask($input, $output, $question));

        $default = 'Je vais dans la rue adjacente.';
        $question = new SFQuestion('Please enter fourth response ['.$default.']: ', $default);
        
        array_push($reponsesText, $bundle = $helper->ask($input, $output, $question));

        
        $Question = new Question();
        $Question
        			->setText($QuestionText)
        			->setIp('127.0.0.1')
        			->setDate(new \DateTime())
        			->setMail('admin@localhost')
        			->setUsername('admin')
        			->setValided(true)
        			->setVoted(false)
        			->setDead(false)
        			->setSoftdeleted(false)
        		;
        $em->persist($Question);
        

        foreach ($reponsesText as $text) {
        	$Reponse = new Reponse();
        	$Reponse->setText($text)
        			->setQuestion($Question)
        	;
        	$em->persist($Reponse);
        }

        $em->flush();


        $output->writeln('It\'s ready !');

    }
}
