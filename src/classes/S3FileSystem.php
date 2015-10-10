<?php
use Aws\S3\S3Client;
class S3FileSystem{
	
	var $s3;
	var $key;
	var $secret;
	
	public function __construct($key, $secret){
		
		$this->key = $key;
		$this->secret = $secret;
		$arr = array(
				'key'    => $key,
				'secret' => $secret,
				'region' => AWS_REGION
		);
		$this->s3 = S3Client::factory($arr);	
	}
	
	public function putObject($bucket, $key, $sourceFile, $acl){
		$res = null;
		try{
			$res = $this->s3->putObject(array(
					'Bucket' => $bucket,
					'Key'    => $key,
					'SourceFile'   => $sourceFile,
					'ACL'    => $acl
					/*'ContentType' => 'image/jpeg'*/
			));
		}catch(Exception $e){
			LogManager::getInstance()->info($e->getMessage());
			return NULL;	
		}
		
		LogManager::getInstance()->info("Response from s3:".print_r($res,true));
		
		$result = $res->get('RequestId');
		if(!empty($result)){
			return 	$result;
		}
		
		return NULL;
	}
	
	public function deleteObject($bucket, $key){
		$res = null;
		
		try{
			$res = $this->s3->deleteObject(array(
					'Bucket' => $bucket,
					'Key'    => $key
			));
		}catch(Exception $e){
			LogManager::getInstance()->info($e->getMessage());
			return NULL;	
		}
		
		LogManager::getInstance()->info("Response from s3:".print_r($res,true));
		
		$result = $res->get('RequestId');
		if(!empty($result)){
			return 	$result;
		}
		
		return NULL;
	}
	
	public function generateExpiringURL($url, $expiresIn = 600) {
		// Calculate expiry time
		$expiresTimestamp = time() + intval($expiresIn);
		$path = parse_url($url, PHP_URL_PATH);
		$path = str_replace('%2F', '/', rawurlencode($path = ltrim($path, '/')));
		$host = parse_url($url, PHP_URL_HOST);
		$bucket = str_replace(".s3.amazonaws.com", "", $host);
		// Path for signature starts with the bucket
		$signpath = '/'. $bucket .'/'. $path;
	
		// S3 friendly string to sign
		$signsz = implode("\n", $pieces = array('GET', null, null, $expiresTimestamp, $signpath));
	
		// Calculate the hash
		$signature = $this->el_crypto_hmacSHA1($this->secret, $signsz);
		// ... to the query string ...
		$qs = http_build_query($pieces = array(
				'AWSAccessKeyId' => $this->key,
				'Expires' => $expiresTimestamp,
				'Signature' => $signature,
		));
		// ... and return the URL!
		return $url.'?'.$qs;
	}
	
	private function el_crypto_hmacSHA1($key, $data, $blocksize = 64) {
		if (strlen($key) > $blocksize) $key = pack('H*', sha1($key));
		$key = str_pad($key, $blocksize, chr(0x00));
		$ipad = str_repeat(chr(0x36), $blocksize);
		$opad = str_repeat(chr(0x5c), $blocksize);
		$hmac = pack( 'H*', sha1(
				($key ^ $opad) . pack( 'H*', sha1(
						($key ^ $ipad) . $data
				))
		));
		return base64_encode($hmac);
	}
	
}