<?php

namespace App\Controller;

use App\Data\ICU;
use Salarmehr\Cosmopolitan\Cosmo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ConvertController extends AbstractController
{

    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    //  Return languages list. 
    #[Route('/api/languages', name: 'app_language_list', methods: 'GET')]
    function languageList(): JsonResponse
    {
        $languages = ICU::get_languages();

        return $this->json([
            'message' => 'Languages fetched successfully!',
            'data' => $languages
        ]);
    }

    // Spellout numbers based on language. 
    #[Route('/api/convert', name: 'app_spellout_convert', methods: ['POST', 'OPTIONS'])]
    function spellout(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $validationResult = $this->validateRequest($data);

        if (count($validationResult) > 0) {
            return $this->json([
                'message' => 'We have some error',
                'data' => $validationResult
            ], 400);
        }

        $result = Cosmo::create($data['language'])->spellout(floatval($data['number']));

        return $this->json([
            'message' => 'Data Converted successfully!',
            'data' => ["spellout" => $result]
        ]);
    }

    // Validate convert body json fields
    function validateRequest($data): array
    {
        $messages = [];
        $constraints = new Assert\Collection([
            'language' => [
                new Assert\NotBlank(),
            ],
            'number' => [
                new Assert\NotBlank()
            ],
        ]);
        $errors = $this->validator->validate($data, $constraints);
        foreach ($errors as $error) {
            // dd($error);
            array_push($messages, $error->getPropertyPath() . "  " . $error->getMessage());
        }
        return $messages;
    }
}
