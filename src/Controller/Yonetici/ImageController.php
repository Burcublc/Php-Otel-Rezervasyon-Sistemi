<?php

namespace App\Controller\Yonetici;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/yonetici/image")
 */
class ImageController extends AbstractController
{
    /**
     * @Route("/", name="image_index", methods={"GET"})
     */
    public function index(ImageRepository $imageRepository): Response
    {
        return $this->render('yonetici/image/index.html.twig', [
            'images' => $imageRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{id}", name="image_new", methods={"GET","POST"})
     */
    public function new(Request $request,$id,ImageRepository $imageRepository): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            ///////////dosya indirme/////////////////
            $file = $form['image']->getData();
            if($file){
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('images_directory'), //servis.yaml dosyasında tanımldık bu ismi, servis.yaml config dosyasındadır.
                        $fileName
                    );
                } catch(FileException $e){

                }
                $image->setImage($fileName);
            }
            ///////////////////////////////
            $entityManager->persist($image);
            $entityManager->flush();

            return $this->redirectToRoute('image_new',['id' => $id]);//burda id 'yi göndermezsek çalışmaz çünkü id'yi implement ettik yukarda
        }

        $images = $imageRepository->findBy(['hotel'=>$id]);//burda tüm otellerin resimlerine değilde o otelin resimlerini getirecek

        return $this->render('yonetici/image/new.html.twig', [
            'id'=> $id,
            'images' => $images,//veritabanı değişkeni
            'image' => $image,//form değişkeni(formu name'i)
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="image_show", methods={"GET"})
     */
    public function show(Image $image): Response
    {
        return $this->render('yonetici/image/show.html.twig', [
            'image' => $image,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="image_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Image $image): Response
    {
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('image_index');
        }

        return $this->render('yonetici/image/edit.html.twig', [
            'image' => $image,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/{hid}", name="image_delete", methods={"DELETE"})
     */
    public function delete(Request $request,$hid, Image $image): Response
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($image);
            $entityManager->flush();
        }

        return $this->redirectToRoute('image_new',['id'=>$hid]);
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }
}
