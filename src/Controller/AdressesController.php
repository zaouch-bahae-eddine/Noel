<?php

namespace App\Controller;


use App\Entity\Adresses;
use App\Form\AdressesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class AdressesController extends AbstractController
{
    /**
     * @Route("/adresses", name="adresses")
     */
    public function index(Request $request): Response
    {
        $dataForm = new Adresses();
        $form = $this->createForm(AdressesType::class, $dataForm);
        if ($request->isXmlHttpRequest()) {
            $adresses = $this->getDoctrine()->getRepository(Adresses::class)->findAll();
            $i = 0;
            foreach ($adresses as $adresse) {
                $ligne = [
                    "nomRue" => $adresse->getNomRue(),
                    "numRue" => $adresse->getNumeRue(),
                    "codePostal" => $adresse->getCodePostal(),
                    "ville" => $adresse->getVille(),
                ];
                $dataJson[$i] = $ligne;
                $i++;
            }
            $donnees = [
                ["nomRue" => "motte", "numRue" => "75", "codePostal" => 8000, "ville" => "amiens"],
                ["nomRue" => "rara", "numRue" => "57", "codePostal" => 8000, "ville" => "paris"],
                ["nomRue" => "eaea", "numRue" => "27", "codePostal" => 8000, "ville" => "lyon"],
            ];
            return new JsonResponse($donnees);
        } else {

            return $this->render('adresses/index.html.twig', ['adressesFormAjouter' => $form->createView()]);
        }
    }

    /**
     * @Route("/adresses/ajouter", name="ajouter_adresses", methods={"POST"})
     */
    public function ajouterAction(Request $request): Response
    {
        if ($request->isXmlHttpRequest()){
            $data = $request->getContent();
            $objectNormalizer = new ObjectNormalizer();
            $normalizers = [$objectNormalizer];
            $encoders = [new JsonEncoder()];
            $serializer = new Serializer($normalizers, $encoders);
            $result = $serializer->deserialize($data, Adresses::class, 'json');
            $em = $this->getDoctrine()->getManager();
            $em->persist($result);
            $em->flush();
            $successMsg = [
                ["statusCode" => 200, "statusMsg" => "ok"]
            ];
            return new JsonResponse($successMsg);
        }
        $failMsg = [
            ["statusCode" => 404, "statusMsg" => "page non trouvee"]
        ];
        return new JsonResponse($failMsg);
    }
}