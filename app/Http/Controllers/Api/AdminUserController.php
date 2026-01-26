<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
	public function index(Request $request): JsonResponse
	{
		$query = User::withCount('tournaments');

		if ($request->has('search')) {
			$search = $request->get('search');
			$query->where(function ($q) use ($search) {
				$q->where('name', 'like', "%{$search}%")
					->orWhere('email', 'like', "%{$search}%");
			});
		}

		if ($request->has('banned')) {
			$banned = filter_var($request->get('banned'), FILTER_VALIDATE_BOOLEAN);
			if ($banned) {
				$query->whereNotNull('banned_at');
			} else {
				$query->whereNull('banned_at');
			}
		}

		$users = $query->orderBy('created_at', 'desc')
			->paginate($request->get('per_page', 15));

		return response()->json($users);
	}

	public function show(User $user): JsonResponse
	{
		return response()->json($user);
	}

	public function ban(User $user): JsonResponse
	{
		if ($user->id === auth()->id()) {
			return response()->json(['message' => 'Cannot ban yourself'], 422);
		}

		$user->update(['banned_at' => now()]);

		return response()->json(['message' => 'User banned successfully', 'user' => $user]);
	}

	public function unban(User $user): JsonResponse
	{
		$user->update(['banned_at' => null]);

		return response()->json(['message' => 'User unbanned successfully', 'user' => $user]);
	}

	public function makeAdmin(User $user): JsonResponse
	{
		if ($user->id === auth()->id()) {
			return response()->json(['message' => 'Cannot change your own admin status'], 422);
		}

		$user->update(['is_admin' => true]);

		return response()->json(['message' => 'User promoted to admin successfully', 'user' => $user]);
	}

	public function removeAdmin(User $user): JsonResponse
	{
		if ($user->id === auth()->id()) {
			return response()->json(['message' => 'Cannot change your own admin status'], 422);
		}

		$user->update(['is_admin' => false]);

		return response()->json(['message' => 'Admin rights removed successfully', 'user' => $user]);
	}

	public function destroy(User $user): JsonResponse
	{
		if ($user->id === auth()->id()) {
			return response()->json(['message' => 'Cannot delete yourself'], 422);
		}

		$user->delete();

		return response()->json(['message' => 'User deleted successfully']);
	}
}
