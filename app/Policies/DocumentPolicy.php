<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Document $document): bool
    {
        return $user->company_id === $document->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Document $document): bool
    {
        return $user->company_id === $document->company_id && $document->status === 'PENDING';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->company_id === $document->company_id
            && $document->status === 'PENDING'
            && $user->hasAnyRole(['admin', 'super-admin']);
    }

    /**
     * Determine whether the user can send the document to SUNAT.
     */
    public function sendToSunat(User $user, Document $document): bool
    {
        return $user->company_id === $document->company_id
            && $document->status === 'PENDING';
    }

    /**
     * Determine whether the user can cancel the document.
     */
    public function cancel(User $user, Document $document): bool
    {
        return $user->company_id === $document->company_id
            && in_array($document->status, ['PENDING', 'SENT', 'ACCEPTED'])
            && $user->hasAnyRole(['admin', 'super-admin']);
    }
}
