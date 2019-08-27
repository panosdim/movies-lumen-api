<?php
namespace App\Http\Controllers;

use App\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller as BaseController;

class AuthController extends BaseController
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;
    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /**
     * Create a new token.
     *
     * @param  \App\User   $user
     * @return string
     */
    protected function jwt(User $user)
    {
        $payload = [
            'iss' => 'lumen-jwt', // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + 60 * 60, // TODO: Expiration time set to 1 hour
        ];

        // As you can see we are passing `JWT_SECRET` as the second parameter that will
        // be used to decode the token in the future.
        return JWT::encode($payload, env('JWT_SECRET'));
    }
    /**
     * Authenticate a user and return the token if the provided credentials are correct.
     *
     * @param  \App\User   $user
     * @return mixed
     */
    public function authenticate(User $user)
    {
        $this->validate($this->request, [
            'email'    => 'required|email',
            'password' => 'required',
        ]);
        // Find the user by email
        $user = User::where('email', $this->request->input('email'))->first();
        if (!$user) {
            // You wil probably have some sort of helpers or whatever
            // to make sure that you have the same response format for
            // different kind of responses. But let's return the
            // below response for now.
            return response()->json([
                'error' => 'Email does not exist.',
            ], 400);
        }
        // Verify the password and generate the token
        if (Hash::check($this->request->input('password'), $user->password)) {
            return response()->json([
                'token' => $this->jwt($user),
                'user'  => $user,
            ], 200);
        }
        // Bad Request response
        return response()->json([
            'error' => 'Email or password is wrong.',
        ], 400);
    }

    public function register()
    {
        $this->validate($this->request, [
            'email'     => 'required|email',
            'password'  => 'required',
            'firstName' => 'required',
            'lastName'  => 'required',
        ]);

        // Validate CAPTCHA
        $captcha = null;
        $secret = env('CAPTCHA_KEY');

        // Stream options
        $opts = [
            'http' => [
                'request_fulluri' => true,
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ]
        ];

        // Create a stream
        $context = stream_context_create($opts);

        if (isset($_POST['g-recaptcha-response'])) {
            $captcha = $_POST['g-recaptcha-response'];
        }

        if (is_null($captcha)) {
            return response()->json([
                'error' => 'Please check the captcha form.',
            ], 422);
        }

        $response = json_decode(
            file_get_contents(
                "https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$captcha}",
                false,
                $context
            ),
            true
        );

        if ($response['success'] == false) {
            return response()->json([
                'error' => 'Captcha verification failed.',
            ], 422);
        }

        // Find the user by email
        $user = User::where('email', $this->request->input('email'))->first();
        if ($user) {
            return response()->json([
                'error' => 'User with same Email already exists.',
            ], 422);
        }

        $user = User::create([
            'email'      => $this->request->email,
            'password'   => Hash::make($this->request->password),
            'first_name' => $this->request->firstName,
            'last_name'  => $this->request->lastName,
        ]);

        return response()->json($user, 201);
    }
}
