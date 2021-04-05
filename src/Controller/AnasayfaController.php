<?php

namespace App\Controller;

use App\Entity\Hotel;
use App\Entity\Messages;
use App\Entity\Setting;
use App\Entity\User;
use App\Form\MessagesType;
use App\Repository\CommentRepository;
use App\Repository\HotelRepository;
use App\Repository\RoomRepository;
use App\Repository\SettingRepository;
use App\Repository\UserRepository;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailTransportFactory;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;


class AnasayfaController extends AbstractController
{
    /**
     * @Route("/anasayfa", name="anasayfa")
     */
    public function index(SettingRepository  $settingRepository,HotelRepository $hotelRepository)
    {
        $data=$settingRepository->findAll();
        //array findBy(array $criteria,array $orderBy=null,int|null $limit=null,int|null $offset=null)//aşağıdakinin açıklamalı hali içindekilerinin
        $slider=$hotelRepository->findBy([],['title'=>'ASC'],3);
        $hotelresim=$hotelRepository->findBy([],['title'=>'DESC'],4);
        $newhotelresim=$hotelRepository->findBy([],['title'=>'DESC'],10);

        //$slider=$hotelRepository->findAll();//herhangi bir parametre içine almıyor bütün sonuçları getiriyor

        return $this->render('anasayfa/index.html.twig', [
            'controller_name' => 'AnasayfaController',
            'data'=> $data,
            'slider'=> $slider,
            'hotelresim'=>$hotelresim,
            'newhotelresim'=>$newhotelresim,
        ]);
    }

    /**
     * @Route("/hotel/{id}", name="kullanici_hotel_show", methods={"GET"})
     */
    public function hotelshow(Hotel $hotel,$id,ImageRepository $imageRepository,CommentRepository $commentRepository,RoomRepository $roomRepository): Response
    {
        $otelresim = $imageRepository->findBy(['hotel'=>$id]);
        $yorumgoster = $commentRepository->findBy(['hotelid'=>$id,'status'=>'Yeni']);
        $room = $roomRepository->findBy(['hotelid'=>$id,'status'=>'Aktif']);
        return $this->render('anasayfa/hotelshow.html.twig', [
            'hotel' => $hotel,
            'otelresim'=> $otelresim,
            'yorumgoster'=> $yorumgoster,
            'room' => $room,

        ]);
    }
    /**
     * @Route("/hakkimizda", name="anasayfa_hakkimizda")
     */
    public function  hakkimizda(SettingRepository  $settingRepository): Response
    {
        $data=$settingRepository->findAll();
        return $this->render('anasayfa/aboutus.html.twig', [
            'data' => $data,
        ]);
    }
    /**
     * @Route("/iletisim", name="anasayfa_iletisim", methods={"GET","POST"})
     */
    public function  iletisim(SettingRepository  $settingRepository,Request $request): Response
    {
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);
        $submittedToken= $request->request->get('token');//csrf kontrolü için key:token yaptık ve valid olduktan sonra aşağıda if kontrolüyle csrfin sağlanıp sağlanmadığını gördük
        $setting=$settingRepository->findAll();
        if ($form->isSubmitted()) {
            if($this->isCsrfTokenValid('form-messeage',$submittedToken)) {//dışarıdan bir mesaj girdiğimiz için csrf kontrolü sağlamamız gerekiyor
                $entityManager = $this->getDoctrine()->getManager();
                $message->setStatus('Yeni');
                $message->setIp($_SERVER['REMOTE_ADDR']);
                $entityManager->persist($message);
                $entityManager->flush();
                $this->addFlash('success','Mesajınız Başarıyla Gönderilmiştir.');//ekranda bu mesaj görülecek

                //email gönderme------------------------------------------------------------

                $email=(new Email())
                    ->from($setting[0]->getSmtpEmail())
                    ->to($form['email']->getData())
                    ->subject('All Holiday Your Request')
                    ->html("Dear ".$form['name']->getData() ."<br>
                                  <p>We will evaluate your request and contact you as soon as possible</p>
                                  Thank you for your message <br>
                                  ========================================================
                                  <br>".$setting[0]->getCompany()." <br>
                                  Address : ".$setting[0]->getAddress()."<br>
                                  Phone :   ".$setting[0]->getPhone()."<br>"
                    );
                $transport = new GmailSmtpTransport($setting[0]->getSmtpemail(),$setting[0]->getSmtppassword());
                $mailer =new Mailer($transport);
                $mailer->send($email);
                //----------------------------------------------------------------------------
                return $this->redirectToRoute('anasayfa_iletisim');
            }
        }

        $data=$settingRepository->findAll();
        return $this->render('anasayfa/contact.html.twig', [
            'setting'=> $setting,
            'data' => $data,
            'form' => $form->createView(),
        ]);
    }



}
