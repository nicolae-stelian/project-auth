<?php

namespace App\Controller;

use App\Entity\Link;
use App\Entity\User;
use App\Repository\LinkRepository;
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
    public function checkEmail($email, UserRepository $userRepository, UserSerializer $serializer)
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
     *     description="The user is succesffully"
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
    ) {
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
     * @param \Swift_Mailer $mailer
     * @return Response
     *
     * @throws \Exception
     */
    public function signUp(
        Request $request,
        UserRepository $userRepository,
        UserSerializer $serializer,
        ContentParser $parser,
        \Swift_Mailer $mailer
    ) {
        $content = $parser->parse($request->getContent());

        // check if email already exists in database
        $user = $userRepository->findOneBy(['email' => $content['email']]);
        if ($user) {
            $response = ["message" => "Email already exists in our database"];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        // validate email field
        if (!filter_var($content['email'], FILTER_VALIDATE_EMAIL)) {
            $response = ["message" => "Invalid email."];
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
        $body = $this->renderView('emails/registration.html.twig', ['link' => $link]);

        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('stelu26@gmail.com')
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html')
        ;
        $mailer->send($message);

        // 4. return the user object
        return new JsonResponse($serializer->toJsonString($user), Response::HTTP_OK, [], true);
    }

    /**
     * Validate user registration.
     *
     * @Route("/validate/{link}", name="validate", methods="GET")
     *
     * @param Request $request
     * @param string $link
     * @param UserRepository $userRepository
     * @param LinkRepository $linkRepository
     * @return Response
     *
     * @throws \Exception
     */
    public function validate(Request $request, $link, UserRepository $userRepository, LinkRepository $linkRepository)
    {
        $acceptsJson = in_array('application/json', $request->getAcceptableContentTypes());

        $link = $linkRepository->findOneBy(['link' => $link]);
        if (!$link) {
            $response = ["message" => "Invalid activation link."];
            return $this->createValidationLinkResponse($acceptsJson, $response, Response::HTTP_NOT_FOUND);
        }

        $user = $userRepository->find($link->getUserId());

        // user is already active
        if ($user->getActive()) {
            $response = ["message" => "User already active."];
            return $this->createValidationLinkResponse($acceptsJson, $response, Response::HTTP_NOT_FOUND);
        }

        $user->setActive(true);
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->createValidationLinkResponse($acceptsJson, ['message' => 'Activation successfully']);
    }

    protected function createValidationLinkResponse($acceptsJson, $response, $status = Response::HTTP_OK)
    {
        if ($acceptsJson) {
            return new JsonResponse($response, $status);
        }
        // html content, @TODO create a view
        $content = '<h1>' . $response['message'] . '</h1>';

        return new Response($content, $status);
    }
}
