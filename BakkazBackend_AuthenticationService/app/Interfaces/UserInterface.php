<?php

namespace App\Interfaces;

use App\Models\User; // Import the User model
use Illuminate\Http\Request;

interface UserInterface
{
    /**
     * Retrieves all users from the system.
     *
     * @param Request $request (Optional) May contain filters or pagination parameters.
     * @return array|Collection An array or collection of User models, or an empty array if no users found.
     * @throws \Exception If user retrieval fails.
     */
    static public function getAllUsers(Request $request);

    /**
     * Retrieves a specific user by their ID.
     *
     * @param int $id The user's unique identifier.
     * @return User|null The User model if found, or null if not found.
     * @throws \Exception If user retrieval fails.
     */
    static public function getUserById(int $id);
    /**
     * Updates a user's authentication information (e.g., password, email).
     *
     * @param int $id The user's unique identifier.
     * @param Request $request The request object containing updated information.
     * @return User The updated User model.
     * @throws \Exception If user update fails (e.g., validation errors, database errors).
     */
    static public function updateAuthInformation(int $id, Request $request);

    /**
     * Deletes a user's account.
     *
     * @param int $id The user's unique identifier.
     * @param Request $request (Optional) May contain confirmation details.
     * @return bool True if deletion is successful, false otherwise.
     * @throws \Exception If account deletion fails.
     */
    static public function deleteAccount(int $id, Request $request = null);

    /**
     * Get user stats.
     * @param Request $request (Optional) May contain confirmation details.
     * @return array user stats
     * @throws \Exception If account deletion fails.
     */
    static public function getUsersStats(Request $request);

    /**
     * Suspend user.
     * @param Request $request (Optional) May contain confirmation details.
     * @return bool True if deletion is successful, false otherwise.
     * @throws \Exception If account deletion fails.
     */
    static public function suspendUser(int $id, Request $request);

    public static function getUserByIds(Request $request);

}