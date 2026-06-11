<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * ProfileController — self-service profile management.
 *
 * Beda dengan UserController:
 *   - UserController = owner manage user lain (role:owner)
 *   - ProfileController = setiap user manage profil sendiri (auth:sanctum biasa)
 *
 * Endpoints:
 *   PUT /api/profile          → update name + email
 *   PUT /api/profile/password → ganti password (require current password verification)
 */
class ProfileController extends BaseController
{
    /**
     * Update name & email user yang sedang login.
     * Email unique kecuali untuk dirinya sendiri (rule: unique:users,email,{id}).
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return $this->successResponse(
            $user->fresh(),
            'Profile updated successfully'
        );
    }

    /**
     * Ganti password user yang sedang login.
     *
     * Security: verify CURRENT password dulu sebelum ganti.
     * Mencegah account takeover kalau session/token bocor.
     *
     * Body:
     *   - current_password (required) — password lama untuk verifikasi
     *   - password (required, min 6, confirmed) — password baru
     *   - password_confirmation (required) — konfirmasi password baru (auto-checked by Laravel `confirmed` rule)
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = $request->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse(
                'Password lama tidak sesuai.',
                ['current_password' => ['Password lama tidak sesuai.']],
                422
            );
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->successResponse(
            null,
            'Password berhasil diganti. Login ulang dengan password baru untuk session yang lain.'
        );
    }
}
