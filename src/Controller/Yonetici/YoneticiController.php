<?php

namespace App\Controller\Yonetici;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class YoneticiController extends AbstractController
{
    /**
     * @Route("/yonetici/", name="yonetici")
     */
    public function index()
    {
        return $this->render('yonetici/yoneticianasayfa/index.html.twig', [
            'controller_name' => 'YoneticiController',
        ]);
    }
}
