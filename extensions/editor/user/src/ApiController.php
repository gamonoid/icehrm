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

		// Native SPA data source: resolves a document (access + data) as JSON,
		// reusing the exact logic the legacy page uses (EditorService::resolveDocument).
		self::register(
			REST_API_PATH . 'editor/document', self::GET, function ($pathParams = null) {
				$restEndpoint = new RestEndPoint();

				$resolved = EditorService::resolveDocument([
					'object' => isset($_REQUEST['object']) ? $_REQUEST['object'] : null,
					'id' => isset($_REQUEST['id']) ? $_REQUEST['id'] : null,
					'field' => isset($_REQUEST['field']) ? $_REQUEST['field'] : null,
					'hash' => isset($_REQUEST['hash']) ? $_REQUEST['hash'] : null,
					'view' => (isset($_REQUEST['view']) && $_REQUEST['view'] == '1'),
					'checks' => (isset($_REQUEST['checks']) && $_REQUEST['checks'] == '1'),
					'title' => isset($_REQUEST['title']) ? $_REQUEST['title'] : null,
				]);

				if (empty($resolved['allowed'])) {
					$restEndpoint->sendResponse(new IceResponse(
						IceResponse::ERROR,
						isset($resolved['error']) ? $resolved['error'] : 'Not found',
						403
					));
					return false;
				}

				$restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $resolved));
			}
		);

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

