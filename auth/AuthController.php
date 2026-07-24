<?php

declare(strict_types=1);

final class AuthController
{
    private AuthRepository $repository;

    private Request $request;

    public function __construct()
    {
        $this->repository = new AuthRepository();
        $this->request = new Request();
    }

    /**
     * Register User
     */
    public function register(): void
    {
        $firstname = trim((string) $this->request->input('firstname'));
        $lastname = trim((string) $this->request->input('lastname'));
        $company = trim((string) $this->request->input('company'));
        $email = strtolower(trim((string) $this->request->input('email')));
        $phone = trim((string) $this->request->input('phone'));
        $password = (string) $this->request->input('password');

        // Required Validation
        if (
            empty($firstname) ||
            empty($email) ||
            empty($phone) ||
            empty($password)
        ) {
            Response::error(
                'Please complete all required fields.',
                HTTP_BAD_REQUEST
            );

            return;
        }

        // Email Validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            Response::error(
                'Invalid email address.',
                HTTP_BAD_REQUEST
            );

            return;
        }

        // Email Exists
        if ($this->repository->emailExists($email)) {

            Response::error(
                'Email already registered.',
                HTTP_BAD_REQUEST
            );

            return;
        }

        // Generate User Code
        $lastUserCode = $this->repository->getLastUserCode();

        $userCode = CodeGenerator::generate(
            'USR',
            $lastUserCode
        );

        // Insert User
        $this->repository->register([
            'user_code' => $userCode,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'company' => $company,
            'email' => $email,
            'phone' => $phone,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        Response::success(
            [],
            'Register successfully.'
        );
    }

    /**
     * Login User
     */
    public function login(): void
    {
        $email = strtolower(trim((string) $this->request->input('email')));
        $password = (string) $this->request->input('password');

        // Required Validation
        if (empty($email) || empty($password)) {

            Response::error(
                'Email and password are required.',
                HTTP_BAD_REQUEST
            );

            return;
        }

        // Find User
        $user = $this->repository->findByEmail($email);

        if (!$user) {

            Response::error(
                'Email or password is incorrect.',
                HTTP_UNAUTHORIZED
            );

            return;
        }

        // Check Status
        if ($user['status'] !== 'ACTIVE') {

            Response::error(
                'Your account is inactive.',
                HTTP_FORBIDDEN
            );

            return;
        }

        // Verify Password
        if (!password_verify($password, $user['password'])) {

            Response::error(
                'Email or password is incorrect.',
                HTTP_UNAUTHORIZED
            );

            return;
        }

        // Start Session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Session Lifetime
        $expiredAt = ($user['role'] === 'ADMIN')
            ? time() + (30 * 60)          // 30 Minutes
            : time() + (7 * 24 * 60 * 60); // 7 Days

        $_SESSION['user_code'] = $user['user_code'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['login_time'] = time();
        $_SESSION['expired_at'] = $expiredAt;

        Response::success(
            [],
            'Login successfully.'
        );
    }

    /**
     * User Profile
     */
    public function profile(): void
    {
        AuthMiddleware::handle();

        $user = $this->repository->findByUserCode(
            AuthMiddleware::userCode()
        );

        if (!$user) {

            Response::error(
                'User not found.',
                HTTP_NOT_FOUND
            );

            return;
        }

        Response::success(
            $user,
            'Profile retrieved successfully.'
        );
    }
}