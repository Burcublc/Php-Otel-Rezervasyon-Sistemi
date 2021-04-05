<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\User;
use App\Entity\Image;
use App\Form\CommentType;
use App\Form\ReservationType;
use App\Form\UserType;
use App\Repository\CommentRepository;
use App\Repository\HotelRepository;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('user/show.html.twig');
    }
    /**
     * @Route("/reservations", name="user_reservations", methods={"GET"})
     */
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        $user=$this->getUser();
        $reservations=$reservationRepository->getUserReservation($user->getId());
        //$reservations=$reservationRepository->findBy(['userid'=>$user->getId()]);
        return $this->render('user/reservations.html.twig',[
            'reservations'=>$reservations,

        ]);
    }
    /**
     * @Route("/reservationusershow/{id}", name="user_reservationshow", methods={"GET"})
     */
    public function reservationshow(ReservationRepository $reservationRepository,$id): Response
    {
        $reservation=$reservationRepository->getReservation($id);
        return $this->render('user/reservation_show.html.twig',[
            'reservation'=>$reservation,

        ]);
    }
    /**
     * @Route("/hotels", name="user_hotels", methods={"GET"})
     */
    public function hotels(): Response
    {
        return $this->render('user/hotels.html.twig');
    }

    /**
     * @Route("/comments", name="user_comments", methods={"GET"})
     */
    public function comments(CommentRepository $commentRepository): Response
    {
        $user=$this->getUser();
        $comments=$commentRepository->getAllCommentsUser($user->getId());
        return $this->render('user/comments.html.twig',[
            'comments'=>$comments,
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request,UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            //dosya indirme//
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
                $user->setImage($fileName);
            }
            //dump($user);
            //die();
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->flush();
            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request,$id ,User $user,UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user=$this->getUser();
        if ($user->getId() != $id)
        {
            return $this->redirectToRoute('anasayfa');
        }
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //dosya indirme//
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
                $user->setImage($fileName);
            }
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );


            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    /**
     * @Route("/newcomment/{id}", name="user_new_comment", methods={"GET","POST"})
     */
    public function commentnew(Request $request,$id): Response
    {

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        $submittedToken= $request->request->get('token');
        if ($form->isSubmitted()) {
            if($this->isCsrfTokenValid('comment',$submittedToken)) {
                $entityManager = $this->getDoctrine()->getManager();

                $comment->setStatus('Yeni');
                $comment->setIp($_SERVER['REMOTE_ADDR']);
                $comment->setHotelid($id);
                $user = $this->getUser();
                $comment->setUserid($user->getId());
                $comment->setImage($user->getImage());



                $entityManager->persist($comment);
                $entityManager->flush();
                $this->addFlash('success','Mesajınız Başarıyla Gönderilmiştir.');
                return $this->redirectToRoute('kullanici_hotel_show', ['id' => $id]);
            }
        }
        return $this->redirectToRoute('kullanici_hotel_show',['id' => $id]);
    }

    /**
     * @Route("/reservation/{rid}/{hid}", name="user_new_reservation", methods={"GET","POST"})
     */
    public function newreservation(Request $request,$rid,$hid,HotelRepository $hotelRepository,RoomRepository $roomRepository): Response
    {
        $hotel=$hotelRepository->findOneBy(['id'=>$hid]);
        $room=$roomRepository->findOneBy(['id'=>$rid]);

        $days=$_REQUEST["days"];

        $checkin=$_REQUEST["checkin"];
        $checkin= Date("Y-m-d ", strtotime($checkin ."0 Day"));
        $checkout= Date("Y-m-d ", strtotime($checkin ."$days Day"));//Gün ekleme komutu


        $data["total"]= $days * $room->getPrice();
        $data["days"]=$days;
        $data["checkin"]=$checkin;
        $data["checkout"]=$checkout;


        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        $submittedToken= $request->request->get('token');
        if ($form->isSubmitted() ) {
            if($this->isCsrfTokenValid('roomreservation',$submittedToken)) {
                $entityManager = $this->getDoctrine()->getManager();

                $checkin=date_create_from_format("d-m-Y",$checkin);//datetime formatına dönüştürüyor
                $checkout=date_create_from_format("d-m-Y",$checkout);

                $user = $this->getUser();
                $reservation->setUserid($user->getId());
                $reservation->setHotelid($hid);
                $reservation->setRoomid($rid);
                $reservation->setCheckin($checkin);
                $reservation->setCheckout($checkout);
                $reservation->setDays($days);
                $reservation->setTotal($data["total"]);
                $reservation->setIp($_SERVER['REMOTE_ADDR']);
                $reservation->setStatus('Yeni');
                $reservation->setCreatedAt(new \ DateTime());//anlık oluşturmayı almak için



                $entityManager->persist($reservation);
                $entityManager->flush();
                return $this->redirectToRoute('user_reservations');
            }
        }
        return $this->render('user/newreservation.html.twig', [
            'reservation' => $reservation,
            'hotel'=>$hotel,
            'room'=>$room,
            'data'=>$data,
            'form' => $form->createView(),
        ]);

    }

}
