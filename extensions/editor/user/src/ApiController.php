<?php
namespace EditorUser;

use Classes\BaseService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Utils\SessionUtils;

class ApiController extends IceApiController
{
	public function registerEndPoints() {

        // REST Api post request
        self::register(
            REST_API_PATH . 'editor/save-content', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();

				if (empty($data['hash'])) {
					return new IceResponse(
						IceResponse::ERROR,
						'Hash not found'
					);
				}

				$content = EditorService::getContentByHash($data['hash']);
				$object = EditorService::getRelatedObject($content->object_type, $content->object_id);
				if (empty($object)) {
					$restEndpoint->sendResponse(
						new IceResponse(IceResponse::ERROR, null, 400)
					);
					return false;
				}

				$user = $this->getCurrentUser();

				$permissions = EditorService::getObjectAccess($object, $user);

				if (!in_array('save', $permissions)) {
					$restEndpoint->sendResponse(
						new IceResponse(IceResponse::ERROR, null, 403)
					);
					return false;
				}

				$result = EditorService::updateContent($data['hash'], json_encode($data['data']));

				if (!$result) {
					$restEndpoint->sendResponse(
						new IceResponse(IceResponse::ERROR, null, 503)
					);

					return false;

				}

				$object->editorObjectUpdated();

				$restEndpoint->sendResponse(
					new IceResponse(IceResponse::SUCCESS)
				);
        	}
		);

		self::register(
			REST_API_PATH . 'editor/update-quiz-answers', self::POST, function ($pathParams = null) {

				$restEndpoint = new RestEndPoint();
				$data = $restEndpoint->getRequestBody();

				if (empty($data['hash'])) {
					return new IceResponse(
						IceResponse::ERROR,
						'Hash not found'
					);
				}

				$content = EditorService::getContentByHash($data['hash']);

				if ($content->object_type !== 'LmsEmployeeCourse' && $content->object_type !== 'LmsEmployeeLesson') {
					return new IceResponse(
						IceResponse::ERROR,
						'Not supported'
					);
				}
				$nsTable = \Classes\BaseService::getInstance()->getFullQualifiedModelClassName($content->object_type);
				$obj = new $nsTable();
				$obj->Load('id = ?', [$content->object_id]);

				$newAnswers = json_decode(json_encode($data['data']));

				$result = EditorJs::compareQuizAnswers(json_decode($obj->answers), $newAnswers);

				$content->content = EditorJs::updateQuizAnswers($content->content, $newAnswers);
				$content->Save();

				$restEndpoint->sendResponse(
					new IceResponse(IceResponse::SUCCESS, ['correct' => $result])
				);
			}
		);
	}
}

