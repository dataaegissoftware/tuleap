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

namespace Tuleap\SVN\REST\v1;

class SettingsRepresentation
{
    /**
     * @var CommitRulesRepresentation {@type \Tuleap\SVN\REST\v1\CommitRulesRepresentation} {@required true}
     */
    public $commit_rules;

    /**
     * @var ImmutableTagRepresentation {@type \Tuleap\SVN\REST\v1\ImmutableTagRepresentation} {@required false}
     */
    public $immutable_tags;

    public function build(
        CommitRulesRepresentation $commit_hook_representation,
        ImmutableTagRepresentation $immutable_tag_representation
    ) {
        $this->commit_rules   = $commit_hook_representation;
        $this->immutable_tags = $immutable_tag_representation;
    }
}
