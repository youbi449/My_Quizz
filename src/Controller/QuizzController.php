<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QuizzController extends AbstractController
{

/**
 * @Route("/home", name="homeSecond")
 * @Route("/", name="home")
 */
    public function index()
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page.');
        $category = $this->getDoctrine()
            ->getRepository(Categorie::class)
            ->findAll();

        $em = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser()->getId());
        $user->setScore(0);
        $em->flush();

        return $this->render('quizz/home.html.twig', [
            'controller_name' => 'HomeController',
            'categorie' => $category,
        ]);

    }

    /**
     * @Route("/category/{id_category}", name="category")
     */
    public function preparation(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser()->getId());
        $user->setScore(0);
        $em->flush();
        $input = $request->get('id_category');
        $data = $this->getDoctrine()
            ->getRepository(Categorie::class)
            ->findOneBy(array('id' => $input));
        dump($user);
        return $this->render('quizz/preparation.html.twig', ['categorie' => $data]);
    }

    /**
     * @Route("/category/{id_category}/question/{number}", name="question")
     */
    public function question(Request $request)
    {
        $id_category = $request->get('id_category');
        $question_number = $request->get('number');
        $referer = $request->headers->get('referer');

        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser()->getId());
        $url_from = ['http://127.0.0.1:8000/category/' . $id_category . '/question/' . ($question_number - 1),
            'http://127.0.0.1:8000/category/' . $id_category,
            'http://127.0.0.1:8000/category/' . $id_category . '/question/' . ($question_number - 1) . '/check'];

        if (!in_array($referer, $url_from)) {
            return $this->redirect('/home');
        } elseif ($question_number == 10) {
            return $this->resultat($id_category);
        } else {

            $question = $this->getDoctrine()
                ->getRepository(Question::class)->findBy(array(
                'idCategorie' => $id_category),
                null
            );

            $reponse = $this->getDoctrine()
                ->getRepository(Reponse::class)->findBy(array(
                'idQuestion' => $question[$question_number],
            ));

            dump($user->getScore());
            return $this->render('quizz/quizz.html.twig', [
                'question' => $question,
                'reponse' => $reponse,
                'actual_page' => $question_number,
                'id_category' => $id_category,
            ]);
        }
    }

    /**
     * @Route("/category/{id_category}/question/{number}/check", name="check")
     */
    public function check(Request $request)
    {

        $id_category = $request->get('id_category');
        $question_number = $request->get('number');
        $user_input = $request->get('reponse');
        $reponse_check = $this->getDoctrine()->getRepository(Reponse::class)->find($user_input);
        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser()->getId());
        $actual_score = $user->getScore();

        if ($reponse_check->getReponseExpected()) {
            $this->addFlash('success', 'Good job !');
            $em = $this->getDoctrine()->getManager();
            $user->setScore($actual_score + 1);
            $em->flush();
        } else {
            $this->addFlash('error', 'Arf..');
        }

        $question = $this->getDoctrine()
            ->getRepository(Question::class)->findBy(array(
            'idCategorie' => $id_category),
            null
        );
        $reponse = $this->getDoctrine()
            ->getRepository(Reponse::class)->findBy(array(
            'idQuestion' => $question[$question_number],
        ));

        return $this->render('quizz/check.html.twig', [
            'question' => $question,
            'reponse' => $reponse,
            'actual_page' => $question_number,
            'id_category' => $id_category,
        ]);
    }

    public function resultat($id_category)
    {
        $category = $this->getDoctrine()
            ->getRepository(Categorie::class)
            ->find($id_category);

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->getUser()
                    ->getId());

        $score = $user->getScore();

        return $this->render('quizz/quizz_fini.html.twig', ['categorie' => $category, 'score' => $score]);
    }

    /**
     * @Route("/account/edit/mail", name="edit")
     */
    public function edit()
    {
        $this_mail = $this->getUser()->getEmail();
        dump($this_mail);
        return $this->render('quizz/edit_mail.html.twig', [
            'mail' => $this_mail,
        ]);
    }

    /**
     * @Route("account/edit/mail/submit", name="edit_submit")
     */
     public function change_mail(Request $request){
        $new_mail = $request->get('mail');
        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser()->getId());
        $em = $this->getDoctrine()->getManager();
        $user->setEmail($new_mail);
        $em->flush();
        $this->addflash('mail_success', 'Your mail was successfully edited');
        return $this->index();
     }
}
