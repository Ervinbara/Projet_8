<?php
namespace AppBundle\Security;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class taskVoter extends Voter
{
    // Création de constante
    const TASK_EDIT = 'task_edit';
    const TASK_DELETE = 'task_delete';
    const TASK_DELETE_ANONYMOUS = 'task_delete_anonymous';

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

    protected function voteOnAttribute($attribute, $task, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

//        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
//            return true;
//        }

        // you know $task is a Post object, thanks to `supports()`
        /** @var Task $task */

        switch ($attribute) {
            case self::TASK_EDIT:
                if ($this->decisionManager->decide($token, ['ROLE_ADMIN']) || $user === $task->getAuthor()) {
                    return true;
                }
                else
                    return false;
            case self::TASK_DELETE:
                return $this->canDelete($task, $user);
//            case self::TASK_DELETE_ANONYMOUS:
//                return $this->canDeleteAnonymous($task, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canDelete(Task $task, User $user)
    {
        return $user === $task->getAuthor();
    }
//    private function canDeleteAnonymous(Task $task, User $user)
//    {
//        return $task->getAuthor()->getId() === 5;
//    }
}