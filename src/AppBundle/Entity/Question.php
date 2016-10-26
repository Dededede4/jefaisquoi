<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Reponse;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Question
 *
 * @ORM\Table(name="question")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuestionRepository")
 */
class Question
{
    /**
    * @Assert\Callback
    */
    public function isContentValid($context)
    {
        $reponses = $this->getReponses();

        $count_response = 0;
        foreach ($reponses as $Reponse) {
            if (!empty($Reponse->getText()))
            {
                $count_response++;
            }
        }
        if ($this->getDead())
        {
            /* Il est mort : aucune réponses */
            if($count_response != 0)
            {
                $context
                    ->buildViolation('Il ne peut pas y avoir de réponses car la situation tue le visiteur.') // message
                    ->atPath('reponses')
                    ->addViolation()
                  ;
            }
        }
        else
        {
            /* Il est vivant : deux réponses à quatre */
            if($count_response < 2 ||  $count_response > 4)
            {
                $context
                    ->buildViolation('Il ne peut y avoir que deux à quatres réponses.') // message
                    ->atPath('reponses')
                    ->addViolation()
                  ;
            }
        }
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="string", length=255)
     */
    private $text;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=255)
     */
    private $ip;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     * @Assert\Email(
     *     message = "Votre email n'est pas valide.",
     *     checkMX = false
     * )    
     *
     * @ORM\Column(name="mail", type="string", length=255)
     */
    private $mail;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255)
     */
    private $username;

    /**
     * @var boolean
     *
     * @ORM\Column(name="valided", type="boolean")
     */
    private $valided;

    /**
     * @var boolean
     *
     * @ORM\Column(name="voted", type="boolean")
     */
    private $voted;

    /**
     * @var boolean
     *
     * @ORM\Column(name="dead", type="boolean")
     */
    private $dead;

    /**
     * @var boolean
     *
     * @ORM\Column(name="softdeleted", type="boolean")
     */
    private $softdeleted;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Reponse")
     * @ORM\JoinColumn(name="reponse_id", referencedColumnName="id")
     */
    private $reponse;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Reponse", mappedBy="question", cascade={"persist", "remove"})
     */
    private $reponses;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Vote", mappedBy="question")
     */
    private $votes;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Question
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set ip
     *
     * @param string $ip
     *
     * @return Question
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Question
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set mail
     *
     * @param string $mail
     *
     * @return Question
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail
     *
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return Question
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set valided
     *
     * @param boolean $valided
     *
     * @return Question
     */
    public function setValided($valided)
    {
        $this->valided = $valided;

        return $this;
    }

    /**
     * Get valided
     *
     * @return boolean
     */
    public function getValided()
    {
        return $this->valided;
    }

    /**
     * Set reponse
     *
     * @param \AppBundle\Entity\Reponse $reponse
     *
     * @return Question
     */
    public function setReponse(\AppBundle\Entity\Reponse $reponse = null)
    {
        $this->reponse = $reponse;

        return $this;
    }

    /**
     * Get reponse
     *
     * @return \AppBundle\Entity\Reponse
     */
    public function getReponse()
    {
        return $this->reponse;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reponses = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add reponse
     *
     * @param \AppBundle\Entity\Reponse $reponse
     *
     * @return Question
     */
    public function addReponse(\AppBundle\Entity\Reponse $reponse)
    {
        //$reponse->setQuestion($this);
        $this->reponses[] = $reponse;

        return $this;
    }

    /**
     * Remove reponse
     *
     * @param \AppBundle\Entity\Reponse $reponse
     */
    public function removeReponse(\AppBundle\Entity\Reponse $reponse)
    {
        $this->reponses->removeElement($reponse);
    }

    /**
     * Get reponses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReponses()
    {
        return $this->reponses;
    }

    /**
     * Set reponse
     *
     * @param \Doctrine\Common\Collections\ArrayCollection
     *
     * @return Question
     */
    public function setReponses(\Doctrine\Common\Collections\ArrayCollection $collection)
    {
        $this->reponses = $collection;

        return $this;
    }

    /**
     * Set dead
     *
     * @param boolean $dead
     *
     * @return Question
     */
    public function setDead($dead)
    {
        $this->dead = $dead;

        return $this;
    }

    /**
     * Get dead
     *
     * @return boolean
     */
    public function getDead()
    {
        return $this->dead;
    }

    /**
     * Add vote
     *
     * @param \AppBundle\Entity\Vote $vote
     *
     * @return Question
     */
    public function addVote(\AppBundle\Entity\Vote $vote)
    {
        $this->votes[] = $vote;

        return $this;
    }

    /**
     * Remove vote
     *
     * @param \AppBundle\Entity\Vote $vote
     */
    public function removeVote(\AppBundle\Entity\Vote $vote)
    {
        $this->votes->removeElement($vote);
    }

    /**
     * Get votes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * Set voted
     *
     * @param boolean $voted
     *
     * @return Question
     */
    public function setVoted($voted)
    {
        $this->voted = $voted;

        return $this;
    }

    /**
     * Get voted
     *
     * @return boolean
     */
    public function getVoted()
    {
        return $this->voted;
    }

    /**
     * Set softdeleted
     *
     * @param boolean $softdeleted
     *
     * @return Question
     */
    public function setSoftdeleted($softdeleted)
    {
        $this->softdeleted = $softdeleted;

        return $this;
    }

    /**
     * Get softdeleted
     *
     * @return boolean
     */
    public function getSoftdeleted()
    {
        return $this->softdeleted;
    }
}
