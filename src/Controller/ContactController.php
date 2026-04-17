<?php

namespace App\Controller;
use App\Entity\Contact;
use App\Entity\User;
use Symfony\Component\Mime\Address;
use App\Form\ContactFormType;
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
    public function index(Request $request, EntityManagerInterface $em, MailerInterface $mailer = null): Response
    {
        $contact = new Contact();

        if ($this->getUser()) {
            /** @var User $user */
            $user = $this->getUser();
            $contact->setUser($user);
            $contact->setEmail($user->getUserIdentifier());
            $contact->setNom($user->getNom());
        }

        $form = $this->createForm(ContactFormType::class, $contact, [
            'is_logged_in' => !!$this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact->setCreatedAt(new \DateTimeImmutable());

            // On sauvegarde en base de données
            $em->persist($contact);
            $em->flush();

            // Préparation de l'email (seulement si le mailer est disponible)
            if ($mailer) {
                try {
                    $userLabel = $contact->getUser() ? 'Utilisateur Connecté' : 'Visiteur Anonyme';
                    
                    $email = (new Email())
                        ->from(new Address('noreply@energreen.com', $contact->getNom()))
                        ->replyTo($contact->getEmail())
                        ->to('energreencollab@gmail.com')
                        ->subject('Energreen : Nouveau message de ' . $contact->getNom())
                        ->text(sprintf(
                            "Expéditeur: %s\nEmail: %s\nStatut: %s\n\nMessage:\n%s",
                            $contact->getNom(),
                            $contact->getEmail(),
                            $userLabel,
                            $contact->getMessage()
                        ));

                    $mailer->send($email);
                    $this->addFlash('success', 'Merci ! Votre message a été envoyé.');
                } catch (\Exception $e) {
                    // Si l'envoi d'email échoue, on sauvegarde quand même le message
                    $this->addFlash('success', 'Merci ! Votre message a été enregistré.');
                }
            } else {
                // Si le mailer n'est pas disponible, on sauvegarde quand même
                $this->addFlash('success', 'Merci ! Votre message a été enregistré.');
            }
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}