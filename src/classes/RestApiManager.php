<?php
class RestApiManager{
	
	private static $me = NULL;
	
	var $endPoints = array();
	
	private function __construct(){
		
	}
	
	public static function getInstance(){
		if(empty(self::$me)){
			self::$me = new RestApiManager();	
		}
		
		return self::$me;
	}
	
	public function generateUserAccessToken($user){
		
		$data = array();
		$data['userId'] = $user->id;
		$data['expires'] = strtotime('now') + 60*60;
		
		$accessTokenTemp = AesCtr::encrypt(json_encode($data), $user->password, 256);
		$accessTokenTemp = $user->id."|".$accessTokenTemp;
		$accessToken = AesCtr::encrypt($accessTokenTemp, APP_SEC, 256);
		
		return new IceResponse(IceResponse::SUCCESS, $accessToken);
	}
	
	public function getAccessTokenForUser($user){
		$accessTokenObj = new RestAccessToken();
		$accessTokenObj->Load("userId = ?",array($user->id));

		$generateAccessToken = false;
		$accessToken = $accessTokenObj->token;
		if(!empty($accessToken)){
			$resp = $this->validateAccessTokenInner($accessToken);
			if($resp->getStatus() != IceResponse::SUCCESS){
				$generateAccessToken = true;
			}
		}else{
			$generateAccessToken = true;
		}
		
		if($generateAccessToken){
			$accessToken = $this->generateUserAccessToken($user)->getData();
			if(!empty($accessTokenObj->id)){
				$accessTokenObj->token = $accessToken;
				$accessTokenObj->hash = md5(CLIENT_BASE_URL.$accessTokenObj->token);
				$accessTokenObj->updated = date("Y-m-d H:i:s");
				$accessTokenObj->Save();
			}else{
				$accessTokenObj = new RestAccessToken();
				$accessTokenObj->userId = $user->id;
				$accessTokenObj->token = $accessToken;
				$accessTokenObj->hash = md5(CLIENT_BASE_URL.$accessTokenObj->token);
				$accessTokenObj->updated = date("Y-m-d H:i:s");
				$accessTokenObj->created = date("Y-m-d H:i:s");
				$accessTokenObj->Save();
			}
		}
		
		return new IceResponse(IceResponse::SUCCESS, $accessTokenObj->hash);
	}
	
	
	public function validateAccessToken($hash){
		$accessTokenObj = new RestAccessToken();
		LogManager::getInstance()->info("AT Hash:".$hash);
		$accessTokenObj->Load("hash = ?",array($hash));
		LogManager::getInstance()->info("AT Hash Object:".json_encode($accessTokenObj));
		if(!empty($accessTokenObj->id) && $accessTokenObj->hash == $hash){
			return $this->validateAccessTokenInner($accessTokenObj->token);
		}
		
		return new IceResponse(IceResponse::ERROR, "Access Token not found");
	}
	
	private function validateAccessTokenInner($accessToken){
		$accessTokenTemp = AesCtr::decrypt($accessToken, APP_SEC, 256);
		$parts = explode("|", $accessTokenTemp);
		
		$user = new User();
		$user->Load("id = ?",array($parts[0]));
		if(empty($user->id) || $user->id != $parts[0] || empty($parts[0])){
			return new IceResponse(IceResponse::ERROR, -1);
		}
		
		$accessToken = AesCtr::decrypt($parts[1], $user->password, 256);
		
		$data = json_decode($accessToken, true);
		if($data['userId'] == $user->id){
			return new IceResponse(IceResponse::SUCCESS, true);
		}
		
		return new IceResponse(IceResponse::ERROR, false);
	}
	
	public function addEndPoint($endPoint){
		$url = $endPoint->getUrl();
		LogManager::getInstance()->info("Adding REST end point for - ".$url);
		$this->endPoints[$url] = $endPoint;	
	}
	
	public function process($type, $url, $parameters){
		
		$accessTokenValidation = $this->validateAccessToken($parameters['access_token']);
		
		if($accessTokenValidation->getStatus() == IceResponse::ERROR){
			return $accessTokenValidation;		
		}
		
		if(isset($this->endPoints[$url])){
			return $this->endPoints[$url]->$type($parameters);
		}
		
		return new IceResponse(IceResponse::ERROR, "End Point ".$url." - Not Found");
	}
}


class RestEndPoint{

	public function process($type , $parameter = NULL){

		$accessTokenValidation = $this->validateAccessToken();
		if($accessTokenValidation->getStatus() == IceResponse::ERROR){
			$resp = $accessTokenValidation;
		}else{
			$resp = $this->$type($parameter);
		}

		if($resp->getStatus() == IceResponse::SUCCESS && $resp->getCode() == null){
			header('Content-Type: application/json');
			http_response_code(200);
			$this->printResponse($resp->getObject());

		}else if($resp->getStatus() == IceResponse::SUCCESS){
			header('Content-Type: application/json');
			http_response_code($resp->getCode());
			$this->printResponse($resp->getObject());

		}else{
			header('Content-Type: application/json');
			http_response_code($resp->getCode());
			$messages = array();
			$messages[] = array(
				"code" => $resp->getCode(),
				"message" => $resp->getObject()
			);
			$this->printResponse(array("error",[$messages]));
		}

	}
	
	public function get($parameter){
		return new IceResponse(IceResponse::ERROR, "Method not Implemented", 404);
	}
	
	public function post($parameter){
		return new IceResponse(IceResponse::ERROR, "Method not Implemented", 404);
	}
	
	public function put($parameter){
		return new IceResponse(IceResponse::ERROR, "Method not Implemented", 404);
	}
	
	public function delete($parameter){
		return new IceResponse(IceResponse::ERROR, "Method not Implemented", 404);
	}

	public function basicValidation($map, $data){
	    $validator = new Validator();
	    $map = $this->getAssocMap($map);
        unset($map['id']);
	    foreach ($data as $key=>$val) {
	        if(!isset($map[$key])){
	            unset($data[$key]);
                continue;
            }

            $vrules = $map[$key];
            if ((!isset($vrules['allow-null']) || $vrules['allow-null'] == false) && $vrules['validation'] != "none"   && empty($data[$key])){
                return new IceResponse(IceResponse::ERROR, "Field should have a value - ".$key, 400);
            } else if(isset($vrules['remote-source'])){
                $class = $vrules['remote-source'][0];
                $obj = new $class();
                $idField = $vrules['remote-source'][1];
                $obj->Load($idField." = ?", array($val));
                if(empty($obj->$idField) || $obj->$idField != $val){
                    if($vrules['allow-null'] == true ){
                        $data[$key] = null;
                    }else{
                        return new IceResponse(IceResponse::ERROR, "Not found - ".$key, 400);
                    }
                }
            }
            if(!isset($vrules['remote-source'])){
                if((!isset($vrules['validation']) || empty($vrules['validation']))){
                    if(!$validator->validateRequired($val)){
                        return new IceResponse(IceResponse::ERROR, "Required field value missing - ".$key, 400);
                    }
                }else if($vrules['validation'] != "none"){
                    $validationRule = "validate". ucfirst($vrules['validation']);
                    if(!$validator->$validationRule($val)){
                        return new IceResponse(IceResponse::ERROR, "Validation failed - ".$key, 400);
                    }
                }
            }

        }

        //check if request has all required fields
        foreach ($map as $key=>$val) {
            $vrules = $map[$key];
            if(!isset($vrules['remote-source'])) {
                if ($vrules['validation'] != "none") {
                    if (!isset($data[$key])) {
                        return new IceResponse(IceResponse::ERROR, "Required field missing - " . $key, 400);
                    }
                }
            }else{
                if (!isset($vrules['allow-null']) || $vrules['allow-null'] == false) {
                    if (!isset($data[$key])) {
                        return new IceResponse(IceResponse::ERROR, "Required field missing - " . $key, 400);
                    }
                }
            }
        }

        return new IceResponse(IceResponse::SUCCESS, null);

    }

    public function getAssocMap($map){
        $amap = array();
        foreach ($map as $item){
            $amap[$item[0]] = $item[1];
        }
        return $amap;
    }

	public function clearObject($obj){
		return BaseService::getInstance()->cleanUpAdoDB($obj);
	}

	public function validateAccessToken(){
		$accessTokenValidation = RestApiManager::getInstance()->validateAccessToken($_REQUEST['access_token']);

		return $accessTokenValidation;
	}

    public function getValidate($parameter,  $data){
        return new IceResponse(IceResponse::SUCCESS, null);
    }

    public function postValidate($parameter, $data){
        return new IceResponse(IceResponse::SUCCESS, null);
    }

    public function putValidate($parameter,  $data){
        return new IceResponse(IceResponse::SUCCESS, null);
    }

    public function deleteValidate($parameter, $data){
        return new IceResponse(IceResponse::SUCCESS, null);
    }
	
	public function cleanDBObject($obj){
		unset($obj->keysToIgnore);
		return $obj;
	}

	public function printResponse($response){
		echo json_encode($response,JSON_PRETTY_PRINT);
	}

    public function getRequestBodyJSON() {
        $rawInput = file_get_contents('php://input');
        return json_decode($rawInput, true);
    }

    public function getRequestBody() {
        $rawInput = file_get_contents('php://input');
        return $rawInput;
    }
}

