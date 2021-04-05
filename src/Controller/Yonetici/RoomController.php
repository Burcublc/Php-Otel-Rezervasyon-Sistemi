<?php

namespace App\Controller\Yonetici;

use App\Entity\Room;
use App\Form\RoomType;
use App\Repository\HotelRepository;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/yonetici/room")
 */
class RoomController extends AbstractController
{
    /**
     * @Route("/", name="room_index", methods={"GET"})
     */
    public function index(RoomRepository $roomRepository): Response
    {
        return $this->render('yonetici/room/index.html.twig', [
            'rooms' => $roomRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{id}", name="room_new", methods={"GET","POST"})
     */
    public function new(Request $request,$id,HotelRepository $hotelRepository,RoomRepository $roomRepository): Response
    {
        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        $hotel=$hotelRepository->findOneBy(['id'=>$id]);
        $rooms=$roomRepository->findBy(['hotelid'=>$id]);
        $submittedToken= $request->request->get('token');
        if ($form->isSubmitted()) {
            if($this->isCsrfTokenValid('roomy',$submittedToken)) {
                $entityManager = $this->getDoctrine()->getManager();
                $room->setHotelid($hotel->getId());
                ////////////dosya indirme//////////////
                $file = $form['image']->getData();
                if ($file) {
                    $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();
                    try {
                        $file->move(
                            $this->getParameter('images_directory'), //servis.yaml dosyasında tanımldık bu ismi, servis.yaml config dosyasındadır.
                            $fileName
                        );
                    } catch (FileException $e) {
                    }
                    $room->setImage($fileName);
                }
                ////////////////////////////////

                $entityManager->persist($room);
                $entityManager->flush();

                return $this->redirectToRoute('room_new', ['id' => $id]);
            }
        }
        //dump($form);
        //die();
        return $this->render('yonetici/room/new.html.twig', [
            'id'=> $id,
            'room' => $room,//formun name 'i
            'rooms' => $rooms,//veritanındaki room tablosundan çekmek istediğimiz verileri bu değişken ile alıyoruz
            'hotel'=>$hotel,
            'form' => $form->createView(),

        ]);
    }

    /**
     * @Route("/{id}", name="room_show", methods={"GET"})
     */
    public function show(Room $room): Response
    {
        return $this->render('yonetici/room/show.html.twig', [
            'room' => $room,
        ]);
    }

    /**
     * @Route("/{id}/edit/{hid}", name="room_edit")
     */
    public function edit(Request $request,$hid,$id,Room $room,HotelRepository $hotelRepository): Response
    {
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);
        $hotel=$hotelRepository->findOneBy((['id'=>$hid]));
        $submittedToken= $request->request->get('token');
        $r = $this->getDoctrine()
            ->getRepository(Room::class)
            ->find($room->getId());
        if($request->isMethod('POST'))
        {
            if($this->isCsrfTokenValid('roomy',$submittedToken)) {
                $r->setTitle($request->request->get("title"));
                $r->setDescription($request->request->get("description"));
                $r->setPrice($request->request->get("price"));
                $r->setNumberofroom($request->request->get("numberofroom"));
                $r->setStatus($request->request->get("status"));
                $r->setImage($request->request->get("image"));
                //dosya indirme--
                $file = $form['image']->getData();
                if ($file) {
                    $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();
                    try {
                        $file->move(
                            $this->getParameter('images_directory'), //servis.yaml dosyasında tanımldık bu ismi, servis.yaml config dosyasındadır.
                            $fileName
                        );
                    } catch (FileException $e) {

                    }
                    $room->setImage($fileName);
                }
                //$entityManager = $this->getDoctrine()->getManager(); (1)  ya bunu kullanırsın flush yapmak için
                //$entityManager->persist($room);
                //$entityManager->flush();

                $this->getDoctrine()->getManager()->flush(); //(2) yada bunu hiç farketmez

                return $this->redirectToRoute('room_new', ['id' => $hid]);
            }
        }
        return $this->render('yonetici/room/edit.html.twig', [
            'id'=>$id,
            'hid'=>$hid,
            'room'=>$room,
            'hotel'=>$hotel,
            'r'=>$r,
            'form' => $form->createView(),


        ]);
    }

    /**
     * @Route("/{id}/{hid}", name="room_delete", methods={"DELETE"})
     */
    public function delete(Request $request,$hid, Room $room): Response
    {
        if ($this->isCsrfTokenValid('delete'.$room->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($room);
            $entityManager->flush();
        }

        return $this->redirectToRoute('room_new',['id'=>$hid]);
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }
}
