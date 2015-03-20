<?php

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * ProgramRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProgramRepository extends EntityRepository
{
  public function getMostDownloadedPrograms($limit = 20, $offset = 0)
  {
    $qb = $this->createQueryBuilder('e');
  	return $qb
  	->select('e')
  	->where($qb->expr()->eq("e.visible", true))
  	->orderBy('e.downloads', 'DESC')
  	->setFirstResult($offset)
  	->setMaxResults($limit)
  	->getQuery()
  	->getResult();
  }

  public function getMostViewedPrograms($limit = 20, $offset = 0)
  {
    $qb = $this->createQueryBuilder('e');
    return $qb
    ->select('e')
    ->where($qb->expr()->eq("e.visible", true))
    ->orderBy('e.views', 'DESC')
    ->setFirstResult($offset)
    ->setMaxResults($limit)
    ->getQuery()
    ->getResult();
  }
  
  public function getRecentPrograms($limit = 20, $offset = 0)
  {
    $qb = $this->createQueryBuilder('e');
    return $qb
    ->select('e')
    ->where($qb->expr()->eq("e.visible", true))
    ->orderBy('e.uploaded_at', 'DESC')
    ->setFirstResult($offset)
    ->setMaxResults($limit)
    ->getQuery()
    ->getResult();
  }

  public function search($query, $limit=10, $offset=0)
  {
    $query_raw = $query;
    $query = '%'.$query.'%';
    $qb_program = $this->createQueryBuilder('e');
    $weighting = "((CASE WHEN e.name LIKE ?1 THEN 10 ELSE 0 END) + (CASE WHEN f.username LIKE ?1 THEN 1 ELSE 0 END) + (CASE WHEN e.description LIKE ?1 THEN 3 ELSE 0 END) + (CASE WHEN e.id LIKE ?1 THEN 15 ELSE 0 END) + (CASE WHEN e.id = ?2 THEN 11 ELSE 0 END))";
    $q2 = $qb_program->getEntityManager()->createQuery("SELECT e, ".$weighting." AS weight FROM Catrobat\AppBundle\Entity\Program e LEFT JOIN e.user f WHERE ".$weighting." > 0  AND e.visible = true ORDER BY weight DESC, e.uploaded_at DESC");
    $q2->setFirstResult($offset);
    $q2->setMaxResults($limit);
    $q2->setParameter(1, $query);
    $q2->setParameter(2, $query_raw);
    $result = $q2->getResult();
    return array_map(function($element){return $element[0];}, $result);
  }

  public function searchCount($query)
  {
    $query = '%'.$query.'%';
    $qb_program = $this->createQueryBuilder('e');
    $weighting = "((CASE WHEN e.name LIKE ?1 THEN 10 ELSE 0 END) + (CASE WHEN f.username LIKE ?1 THEN 1 ELSE 0 END) + (CASE WHEN e.description LIKE ?1 THEN 3 ELSE 0 END))";
    $q2 = $qb_program->getEntityManager()->createQuery("SELECT COUNT(e.id) FROM Catrobat\AppBundle\Entity\Program e LEFT JOIN e.user f WHERE ".$weighting." > 0 AND e.visible = true");
    $q2->setParameter(1, $query);
    $result = $q2->getSingleScalarResult();
    return $result;
  }

  public function getUserPrograms($user_id)
  {
    $qb = $this->createQueryBuilder('e');
    return $qb
    ->select('e')
    ->leftJoin('e.user', 'f')
    ->where($qb->expr()->eq("e.visible", true))
    ->andWhere($qb->expr()->eq("f.id", ":user_id"))
    ->setParameter("user_id", $user_id)
    ->getQuery()
    ->getResult();
  }

  public function getTotalPrograms()
  {
      $qb = $this->createQueryBuilder('e');
      return $qb
          ->select('COUNT (e.id)')
          ->where($qb->expr()->eq("e.visible", true))
          ->orderBy('e.downloads', 'DESC')
          ->getQuery()
          ->getSingleScalarResult();
  }
}
