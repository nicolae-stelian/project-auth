<?php

namespace App\Controller;

use App\Entity\Link;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\ContentParser;
use App\Services\UserSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

/**
 * @Route("api/v1")
 */
class ApiController extends AbstractController
{
    /**
     * Check if the email exists.
     *
     * @Route("/check/{email}", name="check_email", methods="GET")
     *
     * @param string $email
     * @param UserRepository $userRepository
     * @return JsonResponse
     */
    public function checkEmail($email, UserRepository $userRepository, UserSerializer $serializer): Response
    {
        // find the user in database
        $user = $userRepository->findOneBy(['email' => $email]);
        if (!$user) { // if the user do not exists, response with 404
            $response = ["message" => "Email invalid or do not exists"];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($serializer->toJsonString($user), Response::HTTP_OK, [], true);
    }

    /**
     * Check if an email and a password exists
     *
     * @SWG\Parameter(
     *     name="email",
     *     in="formData",
     *     type="string",
     *     description="The email "
     * )
     *
     * @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     type="string",
     *     description="The password "
     * )
     * @SWG\Response(
     *     response=200,
     *     description="The user if succesffully"
     * )
     *
     * @Route("/login", name="login", methods="POST")
     * @param Request $request
     * @param UserRepository $userRepository
     * @param UserSerializer $serializer
     * @param ContentParser $parser
     * @return Response
     */
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserSerializer $serializer,
        ContentParser $parser
    ): Response
    {
        $content = $parser->parse($request->getContent());

        // find the user in database
        $user = $userRepository->findOneBy([
            'email' => $content['email'],
            'password' => $content['password'],
            'active' => 1
        ]);

        if (!$user) { // if the user do not exists, response with 404
            $response = ["message" => "Failed to login."];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($serializer->toJsonString($user), Response::HTTP_OK, [], true);
    }

    /**
     * @SWG\Parameter(
     *     name="email",
     *     in="formData",
     *     type="string",
     *     description="The email "
     * )
     *
     * @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     type="string",
     *     description="The password "
     * )
     * @SWG\Response(
     *     response=200,
     *     description="The user if succesffully created"
     * )
     *
     * @Route("/sign-up", name="sign_up", methods="POST")
     *
     * @param Request $request
     * @param UserRepository $userRepository
     * @param UserSerializer $serializer
     * @param ContentParser $parser
     * @return Response
     *
     * @throws \Exception
     */
    public function signUp(
        Request $request,
        UserRepository $userRepository,
        UserSerializer $serializer,
        ContentParser $parser
    ): Response
    {
        $content = $parser->parse($request->getContent());
        // check if email already exists in database
        $user = $userRepository->findOneBy(['email' => $content['email']]);
        if ($user) {
            $response = ["message" => "Email already exists in our database"];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        // 1. create user in database  as inactive
        $user = new User($content['email'], $content['password'], false);
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        // 2. generate validation url
        $link = new Link($user->getId());
        $this->getDoctrine()->getManager()->persist($link);
        $this->getDoctrine()->getManager()->flush();

        // 3. send email with the validation url
        
    }

    // 4. create validation url
}
