<?php

namespace TrombiBundle\Controller;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpFoundation\Request;
use TrombiBundle\Entity\Category;
use TrombiBundle\Entity\Person;
use TrombiBundle\Entity\Wilder;

class HomeController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $wilder = new Wilder();
        // créatino du formulaire
        $form = $this->createFormBuilder($wilder, ['csrf_protection'=>false])
            ->setMethod('GET')
            ->add('input', SearchType::class, [
            'required'=>false,
            'label'=>'Search',
            'attr'=> ['placeholder'=>'saisir votre recherche']
        ])
            ->add('category', EntityType::class, [
                'class'=>Category::class,
                'choice_label'=>'name',
            ])
        ->getForm();

        $form -> handleRequest($request);


        $input = $category ='';
        // traitement du formulaire validé
        if ($form->isValid() && $form->isSubmitted()) {
//            $data = $form->getData();
//            $input = $data['input'];
            $input = $wilder->getInput();
            $category = $wilder->getCategory();
            $wilders = $em -> getRepository(Person::class)->searchByName($input, $category);

        }
        if (!$input && !$category) {
            $wilders = $em -> getRepository(Person::class)->findAll();
        }


        return $this->render('TrombiBundle:Home:index.html.twig', [
            'wilders' => $wilders,
            'form' => $form->createView(),
        ]);
    }
}
