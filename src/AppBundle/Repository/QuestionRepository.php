<?php

namespace AppBundle\Repository;
use Doctrine\DBAL\Cache\QueryCacheProfile;

/**
 * QuestionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class QuestionRepository extends \Doctrine\ORM\EntityRepository
{
	public function findFirst()
	{
	    $qb = $this->createQueryBuilder('a');
	      $qb
			->orderBy('a.id')
			->setMaxResults(4)
			->where('a.valided = true')
			->leftJoin('a.reponses', 'r')
			->addSelect('r')
		;
  		$query = $qb->getQuery();

    	$query->useQueryCache(true);
        $query->useResultCache(true);
        $query->setResultCacheLifetime(2678400); // One month
        $query->setCacheable(true);
    	
    	return $query->getSingleResult();
	}

	public function findOlds($nbrReponse)
	{
		/*
		SELECT *
		FROM `question` q
		LEFT JOIN reponse r ON q.id = r.question_id
		LEFT JOIN question sq ON r.id = sq.reponse_id
		WHERE sq.id IS NULL
		*/


    	$qb = $this->createQueryBuilder('a');
	      $qb
			
			->setMaxResults($nbrReponse)

			->where('a.valided = true')
			->andWhere('a.dead = false')
			->orderBy('a.id', 'ASC')

			->leftJoin('a.reponses', 'r')
			->leftJoin('r.child', 'c')
			->andWhere('c.id is null')

			->addSelect('r')
			
		;

		$query = $qb->getQuery();
		$query->useQueryCache(true);
        $query->useResultCache(true);
        $query->setResultCacheLifetime(60); // One minute
        $query->setCacheable(true);
    	
    	$QuestionsNoLimited = $query->getResult();
/*
    	$Questions = array();
    	for ($i=0; $i <= $reponsesMax; $i++) { 
    		$Questions[] = $QuestionsNoLimited[$i];
    	}*/

    	return $QuestionsNoLimited;
    }

	public function findLasts($limit = 1)
	{

    	$reponsesMax = 4;

    	$qb = $this->createQueryBuilder('a');
	      $qb
			->setMaxResults($limit * $reponsesMax) // Désolé
			->where('a.valided = true')
			->orderBy('a.id', 'DESC')
			->leftJoin('a.reponses', 'reponses')
			->addSelect('reponses')
		;

		$query = $qb->getQuery();
		$query->useQueryCache(true);
        $query->useResultCache(true);
        $query->setResultCacheLifetime(60); // One minute
        $query->setCacheable(true);
    	
    	$QuestionsNoLimited = $query->getResult();

    	$Questions = array();
    	for ($i=0; $i <= $reponsesMax; $i++) {
    		if (isset($QuestionsNoLimited[$i])){
    			$Questions[] = $QuestionsNoLimited[$i];
    		}
    	}

    	return $Questions;

    	/** Je laisse le brouillon ici, si quelqu'un veut mieux faire **/

    			/*
			SELECT *
			FROM reponse r
			LEFT JOIN (
			    SELECT * from question
			    order by id desc
			    limit 5
			) q on r.question_id = q.id
		*
	    $qb = $this->createQueryBuilder('a');
	      $qb
			->setMaxResults($limit)
			->where('a.valided = true')
			->orderBy('a.date', 'DESC')
			//->leftJoin('a.reponses', 'reponses')
			//->addSelect('reponses')
		;*/

		/*$em = $this->getEntityManager();
    	$query = $em->createQuery("
    		SELECT q, r
    		FROM AppBundle\Entity\Reponse r
    		JOIN (
    			SELECT *
    			FROM AppBundle\Entity\Question q
    			ORDER BY id DESC
    			LIMIT 5
    		) q on q.id = r.question_id
    		");
    	return $query->getResult();*/

	/*    	
    	$qb = $this->createQueryBuilder('q')
					->setMaxResults(5)
		;

		$repo = $this->getEntityManager()->getRepository('AppBundle:Reponse');
    	$qb2 = $repo->createQueryBuilder('r')
    			->from('AppBundle\Entity\Reponse', 'r')
    			->join('('. $qb->getDql() .')', 'Question q');*/

  		//return $qb2


	}

	public function getQuestionFromParentReponse($Reponse)
	{
		$qb = $this->createQueryBuilder('a');
	    $qb
	    	->where('a.reponse = :Reponse')
	    		->setParameter('Reponse', $Reponse)
			->orderBy('a.id')
			//->setMaxResults(1)
			->andWhere('a.valided = true')

			->leftJoin('a.reponses', 'r')
			->addSelect('r')
		;

		$query = $qb->getQuery();

    	$query->useQueryCache(true);
        $query->useResultCache(true);
        $query->setResultCacheLifetime(60); // One minute
        $query->setCacheable(true);

  		return $query
    	->getOneOrNullResult()
    	;

    	/*$query = $qb->getQuery();

    	$query->useQueryCache(true);
        $query->useResultCache(true);
        $query->setResultCacheLifetime(2678400); // One month
        $query->setCacheable(true);
    	
    	return $query->getSingleResult();*/
	}

	public function getNotValidatedQuestions()
	{
		$qb = $this->createQueryBuilder('a');
	    $qb
			->where('a.valided = false')
			->orderBy('a.id')
			->setMaxResults(10)
		;

  		return $qb
    	->getQuery()
    	->getResult()
    	;
	}

	public function purgeNotValidedChild($Reponse)
	{
		$qb = $this->createQueryBuilder('a');
	    $qb
	    	->update()
	    	->set('a.softdeleted', 'true')
			->where('a.valided != true')
			->andWhere('a.reponse = :Reponse')
				->setParameter(':Reponse', $Reponse)
    		->getQuery()
    		->execute();
    	;
	}

	public function count()
	{
		$qb = $this->createQueryBuilder('a');
	    $qb
	    	->select($qb->expr()->count('a'))
			->where('a.valided = true')
    		;

    	$query = $qb->getQuery();

    	$query->useQueryCache(true);
        $query->useResultCache(true);
        $query->setResultCacheLifetime(60); // One minute
        $query->setCacheable(true);
    	
    	return $query->getSingleScalarResult();
	}

	public function countWait()
	{
		$qb = $this->createQueryBuilder('a');
	    $qb
	    	->select($qb->expr()->count('a'))
			->where('a.valided = false')
			->andWhere('a.voted = false')
    		;

    	$query = $qb->getQuery();
    	$query->useQueryCache(true);
        $query->useResultCache(true);
        $query->setResultCacheLifetime(60); // One minute
        $query->setCacheable(true);
    	
    	return $query->getSingleScalarResult();
	}

	public function findQuestionForVote($User)
	{
		$qb = $this->createQueryBuilder('a');
	    $qb
			->where('a.valided = false')

			->leftJoin('a.votes', 'v', 'WITH', 'v.user = :User')
				->setParameter(':User', $User)
			->andWhere('v.id is null')
			->andWhere('a.softdeleted = false')
			->andWhere('a.voted = false')
			->orderBy('a.id')
			->setMaxResults(1)
    	;

    	return $qb->getQuery()
    		->getOneOrNullResult();
    	;
	}
}