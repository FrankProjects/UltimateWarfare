<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Category;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\CategoryRepository;
use FrankProjects\UltimateWarfare\Util\ForumHelper;
use RuntimeException;

final class CategoryActionService
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ForumHelper
     */
    private $forumHelper;

    /**
     * CategoryActionService service
     *
     * @param CategoryRepository $categoryRepository
     * @param ForumHelper $forumHelper
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        ForumHelper $forumHelper
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->forumHelper = $forumHelper;
    }

    /**
     * @param Category $category
     * @param User $user
     */
    public function create(Category $category, User $user): void
    {
        $this->forumHelper->ensureNotBanned($user);
        $this->forumHelper->ensureNoMassPost($user);
        $this->ensureCategoryPermissions($user);

        $this->categoryRepository->save($category);
    }

    /**
     * @param Category $category
     * @param User $user
     */
    public function edit(Category $category, User $user): void
    {
        $this->forumHelper->ensureNotBanned($user);
        $this->forumHelper->ensureNoMassPost($user);
        $this->ensureCategoryPermissions($user);

        $this->categoryRepository->save($category);
    }

    /**
     * @param Category $category
     * @param User $user
     */
    public function remove(Category $category, User $user): void
    {
        $this->forumHelper->ensureNotBanned($user);
        $this->ensureCategoryPermissions($user);

        $this->categoryRepository->remove($category);
    }

    /**
     * @param User $user
     */
    private function ensureCategoryPermissions(User $user): void
    {
        if (!$user->hasRole('ROLE_ADMIN')) {
            throw new RunTimeException('Not enough permissions!');
        }
    }
}
