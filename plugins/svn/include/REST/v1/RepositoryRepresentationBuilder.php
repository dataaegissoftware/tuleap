<?php
/**
 * Copyright (c) Enalean, 2017. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tuleap\REST\v1;

use PFUser;
use Tuleap\Svn\Admin\ImmutableTagFactory;
use Tuleap\Svn\Repository\HookConfigRetriever;
use Tuleap\Svn\Repository\Repository;
use Tuleap\SVN\REST\v1\RepositoryRepresentation;
use Tuleap\Svn\SvnPermissionManager;

class RepositoryRepresentationBuilder
{
    /**
     * @var SvnPermissionManager
     */
    private $permission_manager;

    /**
     * @var HookConfigRetriever
     */
    private $hook_config_retriever;
    /**
     * @var ImmutableTagFactory
     */
    private $immutable_tag_factory;

    public function __construct(
        SvnPermissionManager $permission_manager,
        HookConfigRetriever $hook_config_retriever,
        ImmutableTagFactory $immutable_tag_factory
    ) {
        $this->permission_manager = $permission_manager;
        $this->hook_config_retriever = $hook_config_retriever;
        $this->immutable_tag_factory = $immutable_tag_factory;
    }

    public function build(Repository $repository, PFUser $user)
    {
        if ($this->permission_manager->isAdmin($repository->getProject(), $user)) {
            $representation = new FullRepositoryRepresentation();
            $representation->fullBuild(
                $repository,
                $this->hook_config_retriever->getHookConfig($repository),
                $this->immutable_tag_factory->getByRepositoryId($repository)
            );

            return $representation;
        }

        $representation = new RepositoryRepresentation();
        $representation->build($repository);

        return $representation;
    }
}
