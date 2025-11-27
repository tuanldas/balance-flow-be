<?php

namespace App\Http\Controllers;

use App\Services\Contracts\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function index(): JsonResponse
    {
        $users = $this->userService->getAll();

        return response()->json($users);
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = $this->userService->createUser($validated);

        return response()->json($user, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'password' => 'sometimes|string|min:8',
        ]);

        $updated = $this->userService->update($id, $validated);

        if (! $updated) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['message' => 'User updated successfully']);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->userService->delete($id);

        if (! $deleted) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['message' => 'User deleted successfully']);
    }
}
