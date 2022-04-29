<?php

namespace App\Repository;

use App\Entity\Categorie;
use App\Entity\Question;
use App\Entity\Reponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function getQuestion($id_category,$question_number)
    {
        $question = $this->getDoctrine()
            ->getRepository(Question::class)->findBy(array(
            'idCategorie' => $id_category),
            null
        );

        $reponse = $this->getDoctrine()
            ->getRepository(Reponse::class)->findBy(array(
            'idQuestion' => $question[$question_number],
        ));

        $return = [$question,$reponse];

        return $return;
    }
}
