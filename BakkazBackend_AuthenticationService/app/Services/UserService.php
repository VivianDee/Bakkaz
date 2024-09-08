<?php

namespace App\Services;

use App\Enums\AssetType;
use App\Helpers\ResponseHelpers;
use Illuminate\Http\Request;
use App\Impl\CloudinaryImpl;
use App\Impl\Services\PreferenceImpl;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Interfaces\AssetInterface;
use App\Interfaces\UserInterface;
use Carbon\Carbon;

class UserService implements UserInterface
{
    public static function getAllUsers(Request $request)
    {
        try {
            $searchQuery = $request->query("search");
            $paginate = (bool) $request->query("paginate");
            $perPage = (int) $request->query("per_page", 15);
            $page = (int) $request->query("page", 1);
            $sort = $request->query("sort");

            $query = User::where("deleted", false);

            $query->where("account_type", '!=', 'admin-signup');

            $query->with("logins")
                ->with("locations")
                ->with("passwords")
                ->with("assets")
                ->with("devices");

            if (!$paginate) return ResponseHelpers::success($query->get());

            if ($searchQuery) {
                if (is_numeric($searchQuery)) {
                    $query->where('id', $searchQuery);
                } else {
                    $searchQueryLower = strtolower($searchQuery);
                    $query->whereRaw("LOWER(name) LIKE ?", [
                        "%" . $searchQueryLower . "%",
                    ])->orWhereRaw("LOWER(first_name) LIKE ?", [
                        "%" . $searchQueryLower . "%",
                    ])->orWhereRaw("LOWER(last_name) LIKE ?", [
                        "%" . $searchQueryLower . "%",
                    ]);
                }
            }

            if ($sort) {
                switch ($sort) {
                    case "active":
                        $query->where('active_status', true);
                        break;
                    case "premium":
                        break;
                    case "verified":
                        break;
                    default:
                        break;
                }
            }

            // Sort the posts by creation date
            $query->orderBy("created_at", "desc");

            // Get paginated results
            $users = $query->paginate($perPage, ["*"], "page", $page);

            if ($users) {
                return ResponseHelpers::success(["users" => $users]);
            }

            return ResponseHelpers::notFound();
        } catch (\Throwable $th) {
            return ResponseHelpers::error(message: $th->getMessage());
        }
    }

    public static function getUserById(int $id)
    {
        try {
            $user = User::where("id", $id)
                ->with("logins")
                ->with("locations")
                ->with("passwords")
                ->with("assets")
                ->with("devices")
                ->get()
                ->first();

            if ($user) {
                if ($user->deleted) {
                    return ResponseHelpers::notFound();
                }
                return ResponseHelpers::success(["user" => $user]);
            }

            return ResponseHelpers::notFound();
        } catch (\Throwable $th) {
            return ResponseHelpers::error(message: $th->getMessage());
        }
    }

    public static function getUserByIds(Request $request)
    {
        try {
            $users = User::whereIn("id", $request->user_ids)
                ->where('deleted', 0)
                ->with("logins")
                ->with("locations")
                ->with("passwords")
                ->with("assets")
                ->with("devices")
                ->get();

            if ($users) {
                return ResponseHelpers::success(["user" => $users]);
            }

            return ResponseHelpers::notFound();
        } catch (\Throwable $th) {
            return ResponseHelpers::error(message: $th->getMessage());
        }
    }

    public static function updateAuthInformation(int $id, Request $request)
    {
        try {
            $user = User::findOrFail($id);

            $data = $request->validate([
                "name" => "nullable|string|max:255",
                "first_name" => "nullable|string|max:255",
                "last_name" => "nullable|string|max:255",
                "email" => "nullable|email|max:255|unique:users,email," . $id,
                "country" => "nullable|string|max:255",
                "state" => "nullable|string|max:255",
                "account_type" => "nullable|string|max:255",
                "active_status" => "nullable|boolean",
            ]);

            // Update the user with validated data
            $user->update($data);

            return ResponseHelpers::success(
                message: "User authentication information updated successfully"
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "first_name",
                    "last_name",
                    "name",
                    "email",
                    "country",
                    "state",
                    "account_type",
                    "active_status",
                ])
            );
        } catch (\Exception $e) {
            return ResponseHelpers::success(
                data: ["errors" => $e->getMessage()],
                message: "Failed to update authentication information",
                statusCode: 500
            );
        }
    }

    public static function deleteAccount(int $id, Request $request = null)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id);

            if (!$user) {
                return ResponseHelpers::notFound('User not found');
            }

            if ($user->deleted||$user->deleted_at) {
                return ResponseHelpers::notFound('User not found');
            }

            // Perform a soft delete and keep a deleted flag
            $user->update([
                "email" => $user->email . " - user[{$user->id}]: deleted ",
                "deleted" => true,
                "deleted_at" => now(),
            ]);

            DB::commit();

            return ResponseHelpers::success(
                message: "Account deleted successfully"
            );

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log the exception to help with debugging
            Log::error('Error deleting account', [
                'user_id' => $id,
                'exception' => $th,
            ]);

            return ResponseHelpers::error(
                message: "An error occurred while trying to delete the account"
            );
        }
    }



    public static function getUsersStats(Request $request)
    {
        $users_count = User::where("account_type", '!=', 'admin-signup')->count();

        // Calculate the time 24 hours ago
        $last24Hours = Carbon::now()->subDay();

        $new_users = User::where("account_type", '!=', 'admin-signup')
            ->where('created_at', '>=', $last24Hours)
            ->count();

        $active_users = User::where("account_type", '!=', 'admin-signup')
            ->where('active_status', true)
            ->count();

        $stats = PreferenceImpl::getStats();

        return ResponseHelpers::success(data: [
            "all_users" => $users_count,
            "new_users" => $new_users,
            "active_users" => $active_users,
            "subscribed_users" => $stats['subscribed_users'] ?? null,
            "verified_users" => $stats['verified_users'] ?? null,
        ]);
    }

    public static function suspendUser(int $id, Request $request = null)
    {
        try {
            $user = User::find($id);

            // Check if the user exists
            if (!$user) {
                return ResponseHelpers::notFound('User not found');
            }

            // Toggle the active_status
            $status = !$user->active_status;

            $user->active_status = $status;
            $user->save();

            // Determine the success message
            $message = $status ? "User account restored successfully" : "User account suspended successfully";

            return ResponseHelpers::success(message: $message);
        } catch (\Throwable $th) {
            return ResponseHelpers::error(message: $th->getMessage());
        }
    }
}
