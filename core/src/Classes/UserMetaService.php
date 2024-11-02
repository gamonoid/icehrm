<?php

namespace Classes;

use Model\UserMeta;

class UserMetaService
{
	public static function add( $userId, $key, $value) {
		$userMeta = new UserMeta();
		$userMeta->Load('user_id = ? and meta_key = ?', [$userId, $key]);
		if (empty($userMeta->user_id)) {
			$userMeta->user_id = $userId;
			$userMeta->meta_key = $key;
		}

		$userMeta->meta_value = $value;
		return $userMeta->Save();
	}

	public static function get( $userId, $key) {
		$userMeta = new UserMeta();
		$userMeta->Load('user_id = ? and meta_key = ?', [$userId, $key]);
		if (empty($userMeta->user_id)) {
			return null;
		}

		return $userMeta;
	}
}
