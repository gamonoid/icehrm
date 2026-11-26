<?php

namespace DiscussionsUser\Common\Model;

use Classes\IceResponse;
use Classes\ModuleAccess;
use Model\BaseModel;

/**
 * A channel. Who may read / post / reply is a comma-separated list of user levels in
 * read_levels / post_levels / reply_levels (e.g. "Admin,Manager,Employee"). Admins are
 * always allowed everything whatever the lists say. is_system marks the built-in
 * Announcements channel, which cannot be deleted.
 *
 * The generic service.php path is admin-only: every other caller goes through the
 * extension's own REST endpoints, which apply the per-channel rules.
 */
class DiscussionChannel extends BaseModel
{
    public $table = 'DiscussionChannels';

    public function getAdminAccess()
    {
        return array("get", "element", "add", "save", "delete");
    }

    public function getManagerAccess()
    {
        return array();
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getUserOnlyMeAccess()
    {
        return array();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('discussions', 'user'),
        ];
    }

    /**
     * A built-in channel (Announcements, Public) can never be deleted — not by an
     * admin either. The admin REST endpoint refuses it too; this covers the generic
     * service.php delete path. Home reads and writes the Public channel, so losing
     * it would break the landing page. A non-SUCCESS response aborts the delete
     * (see BaseService::deleteElement()).
     */
    public function executePreDeleteActions($obj)
    {
        if (!empty($obj->is_system)) {
            return new IceResponse(
                IceResponse::ERROR,
                'The ' . $obj->name . ' channel is built in and cannot be deleted.'
            );
        }
        return new IceResponse(IceResponse::SUCCESS, '');
    }
}
