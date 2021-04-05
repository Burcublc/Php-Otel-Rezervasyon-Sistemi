<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    // SQL ile JOIN LEFT:Burda sql kodu yazmak zorundayız.Ama Aşağıdaki getAllCommentaUser fonksiyonunyla aynı işi yapıyo;yani aynı veritabanındaki
    //iki ayrı tabloyu birleştirmemize iki tablodaki verileri aynı kullanmamızı sağlıyo..
    public function getAllComments(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        SELECT C.*,u.name,u.surname,h.title FROM comment c
        JOIN user u ON u.id = c.userid
        JOIN hotel h ON h.id = c.hotelid
        ORDER BY c.id DESC 
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }
    //DOCRİNE İLE JOIN LEFT:Burda sql sorgu yapmamıza gerek yok symfony kendisi veritabanından getiriyo
    public function getAllCommentsUser($userid): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.id,c.subject,c.comment,c.status,h.title,c.rate,c.hotelid,c.created_at')
            ->leftJoin('App\Entity\Hotel', 'h','WITH' , 'h.id=c.hotelid')
            ->where('c.userid = :userid')
            ->setParameter('userid',$userid)

            ->orderBy('c.id', 'DESC');
        $query = $qb->getQuery();
        return $query->execute();
    }
    // /**
    //  * @return Comment[] Returns an array of Comment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
