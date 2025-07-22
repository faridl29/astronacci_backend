<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; 


class UserController extends Controller
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->middleware('auth:api');
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');

        $users = $this->userRepository->getAllUsers($perPage, $search);

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ]
            ]
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->userRepository->getUserById($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user
            ]
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $data = $request->validated();

            if ($request->has('avatar_base64') && !empty($request->avatar_base64)) {
                $avatar = $this->handleAvatarBase64Upload($request->avatar_base64, $user);
                $data['avatar'] = $avatar;
            }

            unset($data['avatar_base64']);

            $updatedUser = $this->userRepository->updateUser($user->id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => $updatedUser
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q', '');
        $perPage = $request->get('per_page', 10);

        if (empty($search)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'users' => [],
                    'pagination' => null
                ]
            ]);
        }

        $users = $this->userRepository->searchUsers($search, $perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ]
            ]
        ]);
    }

    private function handleAvatarBase64Upload(string $base64Data, $user): string
{
    try {
        if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        if (strpos($base64Data, 'data:image') === 0) {
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
        }

        $imageData = base64_decode($base64Data);
        if ($imageData === false) {
            throw new \Exception('Invalid base64 image data');
        }

        $filename = time() . '_' . uniqid() . '.jpg';
        $path = 'avatars/' . $filename;

                $manager = new ImageManager(Driver::class);

        $image = $manager->read($imageData);

        $image->cover(300, 300);

        $encoded = $image->toJpeg(85);

        Storage::disk('public')->put($path, (string) $encoded);

        return $filename;
    } catch (\Exception $e) {
        throw new \Exception('Failed to process avatar image: ' . $e->getMessage());
    }
}
}