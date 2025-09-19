<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
/**
 * @extends ServiceEntityRepository<Review>
 *
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @param $movieId
     * @return QueryBuilder
     * This method returns a query builder object that can be used to fetch reviews by movie id
     */
    public function getReviewsByMovieQueryBuilder($movieId): QueryBuilder
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.movie = :movieId')
            ->setParameter('movieId', $movieId)
            ->orderBy('r.id', 'ASC');
    }
}
