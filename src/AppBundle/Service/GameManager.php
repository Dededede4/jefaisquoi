<?php
namespace AppBundle\Service;

class GameManager
{
    protected $sessionManager;

    public function __construct($sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function create($Question)
    {
    	$uuid = uniqid();
        $hashPrefix = str_shuffle($uuid);

    	$game = $this->getGame(0);
        if (is_null($game))
        {
            $game = array();
            $games_count = 0;
        }
        else{
            $games_count = $this->sessionManager->get('games_count');
        }
        $game_id = $games_count % 100;

        $game = array('question_id' => $Question->getId(), 'hashPrefix' => $hashPrefix, 'score' => 0);

        $games_count++;
        $this->setGame($game, $game_id);

        $this->sessionManager->set('games_count', $games_count);

        $valid_hash = substr( sha1($hashPrefix.$Question->getId()), 0, 16);

        return array(
        	'id' => $game_id,
        	'hash' => $valid_hash,
        	'score' => '0');
    }

    public function defineQuestion($Question, $game_id)
    {
    	$game = $this->getGame($game_id);

    	$game['question_id'] = $Question->getId();
        $game['score']++;

        $this->setGame($game, $game_id);

        $valid_hash = substr( sha1($game['hashPrefix'].$Question->getId()), 0, 16);

        return array(	'score' => $game['score'],
        				'hash' => $valid_hash,
        				'game_id' => $game_id);
    }

    public function canSeeReponseChild($Reponse, $game_id, $test_hash)
    {
    	$game = $this->getGame($game_id);

        if (empty($game)){
            return false;
        }

        $question_id = $Reponse->getQuestion()->getId();
        $hashPrefix = $game['hashPrefix'];
        
        $valid_hash = substr( sha1($hashPrefix.$question_id), 0, 16);

        return ($test_hash === $valid_hash);
    }

    public function canWriteReponseChild($Reponse, $game_id)
    {
    	$game = $this->getGame($game_id);

    	if (!empty($game['can_follow_reponseid']))
        {
            if ($game['can_follow_reponseid'] != $Reponse->getId())
            {
                return false;
            }
        }
        else{
        	return false;
        }
        return array(	'score' => $game['score'],
        				//'hash' => $valid_hash,
        				'game_id' => $game_id);
    }

    public function canWriteElsewhere($game_id, $Reponse)
    {
        $game = $this->getGame($game_id);

        if (empty($game['can_follow_reponseid']))
        {
            return false;
        }

        if ($game['can_follow_reponseid'] == $Reponse->getId())
        {
            return false;
        }

        return true;

    }

    public function allowWriteReponseChild($Reponse, $game_id)
    {
    	$game = $this->getGame($game_id);
    	$game['can_follow_reponseid'] = $Reponse->getId();
    	$this->setGame($game, $game_id);
        
    }

    protected function getGame($game_id){
    	$games = $this->sessionManager->get('games');
    	if (is_null($games) || empty($games[$game_id]))
    	{
    		return null;
    	}
    	return $games[$game_id];
    }

    protected function setGame($game, $game_id)
    {
    	$games = $this->sessionManager->get('games');
    	$games[$game_id] = $game;
    	$this->sessionManager->set('games', $games);
    }
}