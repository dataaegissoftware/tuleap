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

namespace Tuleap\Svn\Repository;

use Project;
use SystemEvent;
use SystemEventManager;
use Tuleap\Svn\Dao;
use Tuleap\Svn\EventRepository\SystemEvent_SVN_DELETE_REPOSITORY;
use Tuleap\Svn\Repository\Exception\CannotDeleteRepositoryException;

class RepositoryDeleter
{
    /**
     * @var \System_Command
     */
    private $system_command;
    /**
     * @var \ProjectHistoryDao
     */
    private $history_dao;
    /**
     * @var Dao
     */
    private $dao;
    /**
     * @var SystemEventManager
     */
    private $system_event_manager;
    /**
     * @var RepositoryManager
     */
    private $repository_manager;

    public function __construct(
        \System_Command $system_command,
        \ProjectHistoryDao $history_dao,
        Dao $dao,
        SystemEventManager $system_event_manager,
        RepositoryManager $repository_manager
    ) {
        $this->system_command       = $system_command;
        $this->history_dao          = $history_dao;
        $this->dao                  = $dao;
        $this->system_event_manager = $system_event_manager;
        $this->repository_manager   = $repository_manager;
    }

    public function delete(Repository $repository)
    {
        $project = $repository->getProject();
        if (! $project) {
            return false;
        }

        $system_path = $repository->getSystemPath();
        if (is_dir($system_path)) {
            return $this->system_command->exec('rm -rf ' . escapeshellarg($system_path));
        }

        return false;
    }

    public function markAsDeleted(Repository $repository)
    {
        if ($repository->canBeDeleted()) {
            $deletion_date = time();
            $repository->setDeletionDate($deletion_date);
            $this->dao->markAsDeleted(
                $repository->getId(),
                $repository->getSystemBackupPath() . "/" . $repository->getBackupFileName(),
                $deletion_date
            );
        } else {
            throw new CannotDeleteRepositoryException(
                $GLOBALS['Language']->getText('plugin_svn', 'delete_repository_exception')
            );
        }
    }

    public function deleteProjectRepositories(Project $project)
    {
        $repositories = $this->repository_manager->getRepositoriesInProject($project);
        foreach ($repositories as $repository) {
            $this->queueRepositoryDeletion($repository);
        }
    }

    /**
     * @return SystemEvent or null
     */
    public function queueRepositoryDeletion(Repository $repository)
    {
        $this->history_dao->groupAddHistory(
            'svn_multi_repository_deletion',
            $repository->getName(),
            $repository->getProject()->getID()
        );

        return $this->system_event_manager->createEvent(
            'Tuleap\\Svn\\EventRepository\\' . SystemEvent_SVN_DELETE_REPOSITORY::NAME,
            $repository->getProject()->getID() . SystemEvent::PARAMETER_SEPARATOR . $repository->getId(),
            SystemEvent::PRIORITY_MEDIUM,
            SystemEvent::OWNER_ROOT
        );
    }
}
