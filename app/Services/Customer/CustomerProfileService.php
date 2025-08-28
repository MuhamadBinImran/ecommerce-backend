<?php

namespace App\Services\Customer;

use App\Models\CustomerProfile;
use App\Models\User;

class CustomerProfileService
{
    /**
     * Return profile array (user + profile).
     */
    public function getProfile(User $user): array
    {
        $profile = $user->customerProfile()->first();
        return array_merge(
            [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            $profile ? $profile->toArray() : []
        );
    }

    /**
     * Update or create profile fields.
     */
    public function updateProfile(User $user, array $data): array
    {
        // Separate user fields (name) from profile fields
        $userFields = array_intersect_key($data, array_flip(['name']));
        $profileFields = array_intersect_key($data, array_flip([
            'phone','address','city','state','postal_code','country'
        ]));

        if (!empty($userFields)) {
            $user->fill($userFields);
            $user->save();
        }

        $profile = CustomerProfile::updateOrCreate(
            ['user_id' => $user->id],
            $profileFields
        );

        return $this->getProfile($user);
    }
}
