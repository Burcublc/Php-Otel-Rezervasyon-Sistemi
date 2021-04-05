<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function getUserReservation($id): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        SELECT r.* , o.title AS roomname,h.title AS hotelname FROM reservation r
        JOIN room o ON o.id = r.roomid 
        JOIN hotel h ON h.id = r.hotelid
        WHERE r.userid = :userid
        ORDER BY r.id DESC 
        ';

        $stmt = $conn->prepare($sql);
        $stmt->execute(['userid'=>$id]);

        return $stmt->fetchAll();
    }

    public function getReservation($id): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        SELECT r.* , o.title AS roomname,h.title AS hotelname,u.name AS username,u.surname AS usersurname FROM reservation r
        JOIN room o ON o.id = r.roomid 
        JOIN hotel h ON h.id = r.hotelid
        JOIN user u ON u.id = r.userid
        WHERE r.id = :id
       
        ';

        $stmt = $conn->prepare($sql);
        $stmt->execute(['id'=>$id]);

        return $stmt->fetchAll();
    }
    public function getReservations($status): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        SELECT r.* , o.title AS roomname,h.title AS hotelname ,u.name AS username,u.surname AS usersurname FROM reservation r
        JOIN room o ON o.id = r.roomid 
        JOIN hotel h ON h.id = r.hotelid
        JOIN user u ON u.id = r.userid
        WHERE r.status = :status
        ';

        $stmt = $conn->prepare($sql);
        $stmt->execute(['status'=>$status]);

        return $stmt->fetchAll();
    }

    // /**
    //  * @return Reservation[] Returns an array of Reservation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Reservation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
