<?php

namespace App\Controller;
use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer, EntityManagerInterface $em): Response
    {
        $contact = new Contact();
        
        // Si l'utilisateur est connecté, on lie l'objet User au Contact
        if ($this->getUser()) {
            $user = $this->getUser();
            $contact->setUser($this->getUser());
            $contact->setEmail($this->getUser()->getUserIdentifier()); // Ou ->getEmail()
            $contact->setNom($contact->getNom());
        }

    
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact->setCreatedAt(new \DateTimeImmutable());

            
            // On sauvegarde en base de données
            $em->persist($contact);
            $em->flush();

            // Préparation de l'email
            $userLabel = $contact->getUser() ? 'Utilisateur Connecté' : 'Visiteur Anonyme';
            
            $email = (new Email())
                ->from($contact->getNom())
                ->replyTo($contact->getEmail())
                ->to('energreencollab@gmail.com') // L'adresse qui reçoit les notifications
                ->subject('Energreen : Nouveau message de ' . $contact->getNom())
                ->text(sprintf(
                    "Expéditeur: %s\nEmail: %s\nStatut: %s\n\nMessage:\n%s",
                    $contact->getNom(),
                    $contact->getEmail(),
                    $userLabel,
                    $contact->getSubject(),
                    $contact->getMessage() // C'est ici qu'on récupère le message
                ));

            $mailer->send($email);

            $this->addFlash('success', 'Merci ! Votre message a été envoyé.');
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}