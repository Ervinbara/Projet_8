<?php
namespace AppBundle\Security;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class taskVoter extends Voter
{
    // these strings are just invented: you can use anything
    const TASK_EDIT = 'task_edit';
    const TASK_DELETE = 'task_delete';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $task)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::TASK_EDIT, self::TASK_DELETE])) {
            return false;
        }

        // Check if task object
        if (!$task instanceof Task) {
            return false;
        }

        return true;
    }

    /**
     * @param $attribute
     * @param Task $task
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $task, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if ($this->decisionManager->decide($token, ['ROLE_ADMIN']) && $task->getAuthor()->getUsername() === "anonyme") {
            return true;
        }
        // you know $task is a Post object, thanks to `supports()`
        /** @var Task $task */

        switch ($attribute) {
            case self::TASK_EDIT:
            case self::TASK_DELETE:
                return $this->canEdit($task, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }


    private function canEdit(Task $task, User $user)
    {
        return $user === $task->getAuthor();
    }
}