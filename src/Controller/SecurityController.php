<?php

namespace App\Controller;

use App\Entity\Adresses;
use App\Entity\Personnes;
use App\Form\PersonnesAdressesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $formPersonne = $this->createForm(PersonnesAdressesType::class);

        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'formPersonne' => $formPersonne->createView(),
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    /**
     * @Route("/inscription", name="inscription")
     */
    public function inscription(Request $request, UserPasswordEncoderInterface $encoder, ValidatorInterface $validator)
    {
        $data = $this->getJson($request);
        if($data["password"] != $data["password2"])
            return $this->json(['error' => "La confirmation de mot de passe n'est pas correcte"], 500);

        $em = $this->getDoctrine()->getManager();
        $adresse = $this->getDoctrine()->getRepository(Adresses::class)->findOneBy([
            'ville' => strtoupper($data["ville"]),
            'nomRue' => strtoupper($data["nomRue"]),
            'numRue' => $data["numRue"],
            'codePostal' => $data["codePostal"],
            ]);
        $naissance = \DateTime::createFromFormat("d/m/Y", $data["naissance"]);
        $user = new Personnes();
        $user
            ->setNaissance($naissance)
            ->setNom($data["nom"])
            ->setSexe($data["sexe"])
            ->setRoles(["ROLE_SOUHAIT"])
            ->setPassword($encoder->encodePassword($user,$data["password"]));

        if($adresse == null){
            $adresse = new Adresses();
            $adresse->setNomRue($data["nomRue"])
                ->setNumRue($data["numRue"])
                ->setVille($data["ville"])
                ->setCodePostal($data["codePostal"]);
            $em->persist($adresse);
        }
        $user->setAdresse($adresse);

        if(count($validator->validate($adresse)) >0){
            return $this->json(['fail' => "Données de la adresse invalid"],422 );
        }
        if(count($validator->validate($user)) >0){
            return $this->json(['fail' => "Données invalid"],422 );
        }
        $em->persist($user);
        $em->flush();
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
        $this->container->get('session')->set('_security_main', serialize($token));
        return $this->json(["success" => "you are connected"]);

    }
    /**
     * @param Request $request
     * @return mixed
     */
    private function getJson(Request $request)
    {

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpException(400, 'Invalid json');
        }
        return $data;
    }
}
