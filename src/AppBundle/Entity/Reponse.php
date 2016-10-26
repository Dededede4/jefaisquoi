<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reponse
 *
 * @ORM\Table(name="reponse")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReponseRepository")
 */
class Reponse
{
    public function __toString(){
        if ($this->getText())
            return $this->getText();
        return '';
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Question", inversedBy="reponses", cascade={"persist"})
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $question;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Question", mappedBy="reponse")
     */
    private $child;


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
     * @return Reponse
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
     * Set question
     *
     * @param \AppBundle\Entity\Question $question
     *
     * @return Reponse
     */
    public function setQuestion(\AppBundle\Entity\Question $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return \AppBundle\Entity\Question
     */
    public function getQuestion()
    {
        return $this->question;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->child = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add child
     *
     * @param \AppBundle\Entity\Question $child
     *
     * @return Reponse
     */
    public function addChild(\AppBundle\Entity\Question $child)
    {
        $this->child[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \AppBundle\Entity\Question $child
     */
    public function removeChild(\AppBundle\Entity\Question $child)
    {
        $this->child->removeElement($child);
    }

    /**
     * Get child
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChild()
    {
        return $this->child[0];
    }
}
