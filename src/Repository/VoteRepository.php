<?php

namespace App\Repository;

use App\Entity\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Vote|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vote|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vote[]    findAll()
 * @method Vote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Vote::class);
    }


    public function getVotePour($id)
	{
		return $this->createQueryBuilder('v')
			->andWhere('v.demande = :id')
			->setParameter('id', $id)
			->andWhere('v.etat = :pour')
			->setParameter('pour', 'Pour')
			->getQuery()
			->getResult()
			;
	}


	public function getVoteContre($id)
	{
		return $this->createQueryBuilder('v')
			->andWhere('v.demande = :id')
			->setParameter('id', $id)
			->andWhere('v.etat = :pour')
			->setParameter('pour', 'Contre')
			->getQuery()
			->getResult()
			;
	}

	public function getVoteAbstention($id)
	{
		return $this->createQueryBuilder('v')
			->andWhere('v.demande = :id')
			->setParameter('id', $id)
			->andWhere('v.etat = :pour')
			->setParameter('pour', 'Abstention')
			->getQuery()
			->getResult()
			;
	}

    // /**
    //  * @return Vote[] Returns an array of Vote objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Vote
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
