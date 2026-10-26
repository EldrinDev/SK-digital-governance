<?php

namespace App\Policies;

use App\Models\Survey;
use App\Models\User;
use App\Policies\Concerns\ChecksWorkspace;
use App\UserRole;
use Illuminate\Auth\Access\Response;

class SurveyPolicy
{
    use ChecksWorkspace;

    /**
     * Determine whether the officer can see the survey and its results.
     */
    public function view(User $user, Survey $survey): Response
    {
        return $this->inOwnWorkspace($user, $survey->barangay_id);
    }

    /**
     * Determine whether the officer can change the questions. Only drafts can change.
     */
    public function update(User $user, Survey $survey): Response
    {
        $workspace = $this->inOwnWorkspace($user, $survey->barangay_id);

        if ($workspace->denied()) {
            return $workspace;
        }

        return $survey->isDraft()
            ? Response::allow()
            : Response::deny('Questions can’t change once the survey is open.');
    }

    /**
     * Determine whether the officer can open or close the survey.
     */
    public function changeStatus(User $user, Survey $survey): Response
    {
        return $this->inOwnWorkspace($user, $survey->barangay_id);
    }

    /**
     * Determine whether a youth can answer: open, their barangay, not answered yet.
     */
    public function answer(User $user, Survey $survey): Response
    {
        if (! $user->hasRole(UserRole::Youth) || $user->barangay_id !== $survey->barangay_id) {
            return Response::denyAsNotFound();
        }

        if (! $survey->isOpen()) {
            return Response::deny('This survey isn’t taking answers.');
        }

        return $survey->responses()->where('user_id', $user->id)->exists()
            ? Response::deny('You already answered this survey.')
            : Response::allow();
    }
}
