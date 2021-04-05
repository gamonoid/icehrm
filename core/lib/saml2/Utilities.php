<?php
/**
 * This file is part of miniOrange SAML plugin.
 *
 * miniOrange SAML plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * miniOrange SAML plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with miniOrange SAML plugin.  If not, see <http://www.gnu.org/licenses/>.
 */

include_once 'xmlseclibs.php';
use \RobRichards\XMLSecLibs\MoXMLSecurityKey;
use \RobRichards\XMLSecLibs\MoXMLSecurityDSig;
use \RobRichards\XMLSecLibs\MoXMLSecEnc;

class Utilities {

    public static function generateID() {
        return '_' . self::stringToHex(self::generateRandomBytes(21));
    }

    public static function stringToHex($bytes) {
        $ret = '';
        for($i = 0; $i < strlen($bytes); $i++) {
            $ret .= sprintf('%02x', ord($bytes[$i]));
        }
        return $ret;
    }

    public static function generateRandomBytes($length, $fallback = TRUE) {

        return openssl_random_pseudo_bytes($length);
    }

    public static function createAuthnRequest($acsUrl, $issuer, $force_authn = 'false') {
        $requestXmlStr = '<?xml version="1.0" encoding="UTF-8"?>' .
                        '<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="' . self::generateID() .
                        '" Version="2.0" IssueInstant="' . self::generateTimestamp() . '"';
        if( $force_authn == 'true') {
            $requestXmlStr .= ' ForceAuthn="true"';
        }
        $requestXmlStr .= ' ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" AssertionConsumerServiceURL="' . $acsUrl .
                        '" ><saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">' . $issuer . '</saml:Issuer></samlp:AuthnRequest>';
        $deflatedStr = gzdeflate($requestXmlStr);
        $base64EncodedStr = base64_encode($deflatedStr);
        $urlEncoded = urlencode($base64EncodedStr);
        update_option('MO_SAML_REQUEST',$base64EncodedStr);

        return $urlEncoded;
    }

	public static function createSAMLRequest($acsUrl, $issuer, $destination, $force_authn = 'false') {

		$requestXmlStr = '<?xml version="1.0" encoding="UTF-8"?>' .
		                 '<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns="urn:oasis:names:tc:SAML:2.0:assertion" ID="' . self::generateID() .
		                 '" Version="2.0" IssueInstant="' . self::generateTimestamp() . '"';
		if( $force_authn == 'true') {
			$requestXmlStr .= ' ForceAuthn="true"';
		}
		$requestXmlStr .= ' ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" AssertionConsumerServiceURL="' . $acsUrl .
		                  '" Destination="' . htmlspecialchars($destination) . '"><saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">' . $issuer . '</saml:Issuer><samlp:NameIDPolicy AllowCreate="true" Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified"
                        /></samlp:AuthnRequest>';
        $samlRequest  = base64_encode($requestXmlStr);
        update_option('MO_SAML_REQUEST',$samlRequest);
		return $requestXmlStr;
	}

    public static function generateTimestamp($instant = NULL) {
        if($instant === NULL) {
            $instant = time();
        }
        return gmdate('Y-m-d\TH:i:s\Z', $instant);
    }

    public static function xpQuery(DOMNode $node, $query)
    {

        static $xpCache = NULL;

        if ($node instanceof DOMDocument) {
            $doc = $node;
        } else {
            $doc = $node->ownerDocument;
        }

        if ($xpCache === NULL || !$xpCache->document->isSameNode($doc)) {
            $xpCache = new DOMXPath($doc);
            $xpCache->registerNamespace('soap-env', 'http://schemas.xmlsoap.org/soap/envelope/');
            $xpCache->registerNamespace('saml_protocol', 'urn:oasis:names:tc:SAML:2.0:protocol');
            $xpCache->registerNamespace('saml_assertion', 'urn:oasis:names:tc:SAML:2.0:assertion');
            $xpCache->registerNamespace('saml_metadata', 'urn:oasis:names:tc:SAML:2.0:metadata');
            $xpCache->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
            $xpCache->registerNamespace('xenc', 'http://www.w3.org/2001/04/xmlenc#');
        }

        $results = $xpCache->query($query, $node);
        $ret = array();
        for ($i = 0; $i < $results->length; $i++) {
            $ret[$i] = $results->item($i);
        }

        return $ret;
    }

    public static function parseNameId(DOMElement $xml)
    {
        $ret = array('Value' => trim($xml->textContent));

        foreach (array('NameQualifier', 'SPNameQualifier', 'Format') as $attr) {
            if ($xml->hasAttribute($attr)) {
                $ret[$attr] = $xml->getAttribute($attr);
            }
        }

        return $ret;
    }

    public static function xsDateTimeToTimestamp($time)
    {
        $matches = array();

        // We use a very strict regex to parse the timestamp.
        $regex = '/^(\\d\\d\\d\\d)-(\\d\\d)-(\\d\\d)T(\\d\\d):(\\d\\d):(\\d\\d)(?:\\.\\d+)?Z$/D';
        if (preg_match($regex, $time, $matches) == 0) {
            echo sprintf("Invalid SAML2 timestamp passed to xsDateTimeToTimestamp: ". htmlspecialchars($time));
            exit;
        }

        // Extract the different components of the time from the  matches in the regex.
        // intval will ignore leading zeroes in the string.
        $year   = intval($matches[1]);
        $month  = intval($matches[2]);
        $day    = intval($matches[3]);
        $hour   = intval($matches[4]);
        $minute = intval($matches[5]);
        $second = intval($matches[6]);

        // We use gmmktime because the timestamp will always be given
        //in UTC.
        $ts = gmmktime($hour, $minute, $second, $month, $day, $year);

        return $ts;
    }

    public static function extractStrings(DOMElement $parent, $namespaceURI, $localName)
    {



        $ret = array();
        for ($node = $parent->firstChild; $node !== NULL; $node = $node->nextSibling) {
            if ($node->namespaceURI !== $namespaceURI || $node->localName !== $localName) {
                continue;
            }
            $ret[] = trim($node->textContent);
        }

        return $ret;
    }

    public static function validateElement(DOMElement $root)
    {
        //$data = $root->ownerDocument->saveXML($root);
        //echo htmlspecialchars($data);

        /* Create an XML security object. */
        $objXMLSecDSig = new MoXMLSecurityDSig();

        /* Both SAML messages and SAML assertions use the 'ID' attribute. */
        $objXMLSecDSig->idKeys[] = 'ID';


        /* Locate the XMLDSig Signature element to be used. */
        $signatureElement = self::xpQuery($root, './ds:Signature');
        //print_r($signatureElement);

        if (count($signatureElement) === 0) {
            /* We don't have a signature element to validate. */
            return FALSE;
        } elseif (count($signatureElement) > 1) {
            echo sprintf("XMLSec: more than one signature element in root.");
            exit;
        }/*  elseif ((in_array('Response', $signatureElement) && $ocurrence['Response'] > 1) ||
            (in_array('Assertion', $signatureElement) && $ocurrence['Assertion'] > 1) ||
            !in_array('Response', $signatureElement) && !in_array('Assertion', $signatureElement)
        ) {
            return false;
        } */

        $signatureElement = $signatureElement[0];
        $objXMLSecDSig->sigNode = $signatureElement;

        /* Canonicalize the XMLDSig SignedInfo element in the message. */
        $objXMLSecDSig->canonicalizeSignedInfo();

       /* Validate referenced xml nodes. */
        if (!$objXMLSecDSig->validateReference()) {
            echo sprintf("XMLSec: digest validation failed");
            exit;
        }

        /* Check that $root is one of the signed nodes. */
        $rootSigned = FALSE;
        /** @var DOMNode $signedNode */
        foreach ($objXMLSecDSig->getValidatedNodes() as $signedNode) {
            if ($signedNode->isSameNode($root)) {
                $rootSigned = TRUE;
                break;
            } elseif ($root->parentNode instanceof DOMDocument && $signedNode->isSameNode($root->ownerDocument)) {
                /* $root is the root element of a signed document. */
                $rootSigned = TRUE;
                break;
            }
        }

        if (!$rootSigned) {
            echo sprintf("XMLSec: The root element is not signed.");
            exit;
        }

        /* Now we extract all available X509 certificates in the signature element. */
        $certificates = array();
        foreach (self::xpQuery($signatureElement, './ds:KeyInfo/ds:X509Data/ds:X509Certificate') as $certNode) {
            $certData = trim($certNode->textContent);
            $certData = str_replace(array("\r", "\n", "\t", ' '), '', $certData);
            $certificates[] = $certData;
            //echo "CertDate: " . $certData . "<br />";
        }

        $ret = array(
            'Signature' => $objXMLSecDSig,
            'Certificates' => $certificates,
            );

        //echo "Signature validated";


        return $ret;
    }



    public static function validateSignature(array $info, MoXMLSecurityKey $key)
    {


        /** @var MoXMLSecurityDSig $objXMLSecDSig */
        $objXMLSecDSig = $info['Signature'];

        $sigMethod = self::xpQuery($objXMLSecDSig->sigNode, './ds:SignedInfo/ds:SignatureMethod');
        if (empty($sigMethod)) {
            echo sprintf('Missing SignatureMethod element');
            exit();
        }
        $sigMethod = $sigMethod[0];
        if (!$sigMethod->hasAttribute('Algorithm')) {
            echo sprintf('Missing Algorithm-attribute on SignatureMethod element.');
            exit;
        }
        $algo = $sigMethod->getAttribute('Algorithm');

        if ($key->type === MoXMLSecurityKey::RSA_SHA1 && $algo !== $key->type) {
            $key = self::castKey($key, $algo);
        }

        /* Check the signature. */
        if (! $objXMLSecDSig->verify($key)) {
            echo sprintf('Unable to validate Signature');
            exit;
        }
    }

    public static function castKey(MoXMLSecurityKey $key, $algorithm, $type = 'public')
    {

        // do nothing if algorithm is already the type of the key
        if ($key->type === $algorithm) {
            return $key;
        }

        $keyInfo = openssl_pkey_get_details($key->key);
        if ($keyInfo === FALSE) {
            echo sprintf('Unable to get key details from XMLSecurityKey.');
            exit;
        }
        if (!isset($keyInfo['key'])) {
            echo sprintf('Missing key in public key details.');
            exit;
        }

        $newKey = new MoXMLSecurityKey($algorithm, array('type'=>$type));
        $newKey->loadKey($keyInfo['key']);

        return $newKey;
    }

    public static function processResponse($currentURL, $certFingerprint, $signatureData,
        SAML2_Response $response, $certNumber,$relayState) {

        $assertion = current($response->getAssertions());

        $notBefore = $assertion->getNotBefore();
        if ($notBefore !== NULL && $notBefore > time() + 60) {
            error_log('Received an assertion that is valid in the future. Check clock synchronization on IdP and SP.');
            return false;
        }

        $notOnOrAfter = $assertion->getNotOnOrAfter();
        if ($notOnOrAfter !== NULL && $notOnOrAfter <= time() - 60) {
            error_log('Received an assertion that has expired. Check clock synchronization on IdP and SP.');
            return false;
        }

        $sessionNotOnOrAfter = $assertion->getSessionNotOnOrAfter();
        if ($sessionNotOnOrAfter !== NULL && $sessionNotOnOrAfter <= time() - 60) {
            error_log('Received an assertion with a session that has expired. Check clock synchronization on IdP and SP.');
            return false;
        }

        /* Validate Response-element destination. */
        $msgDestination = $response->getDestination();
        if(substr($msgDestination, -1) == '/') {
            $msgDestination = substr($msgDestination, 0, -1);
        }
        if(substr($currentURL, -1) == '/') {
            $currentURL = substr($currentURL, 0, -1);
        }

        if ($msgDestination !== NULL && $msgDestination !== $currentURL) {
            echo sprintf('Destination in response doesn\'t match the current URL. Destination is "' .
                htmlspecialchars($msgDestination) . '", current URL is "' . htmlspecialchars($currentURL) . '".');
            exit;
        }

        $responseSigned = self::checkSign($certFingerprint, $signatureData, $certNumber,$relayState);

        if (!$responseSigned) {
            error_log('SAML: Responses  is not signed');
        }

        /* Returning boolean $responseSigned */
        return $responseSigned;
    }


    public static function checkSign($certFingerprint, $signatureData, $certNumber, $relayState) {
        $certificates = $signatureData['Certificates'];
        error_log('SAML: certificate count = '.count($certificates));
        if (count($certificates) === 0) {
            $storedCerts = maybe_unserialize(get_option('saml_x509_certificate'));
            $pemCert = $storedCerts[$certNumber];
        }else{
            $fpArray = array();
            $fpArray[] = $certFingerprint;
            $pemCert = self::findCertificate($fpArray, $certificates, $relayState);
            if($pemCert==false)
                return false;
        }

        $lastException = NULL;

        $key = new MoXMLSecurityKey(MoXMLSecurityKey::RSA_SHA1, array('type'=>'public'));
        $key->loadKey($pemCert);

        try {
            /*
             * Make sure that we have a valid signature
             */
            self::validateSignature($signatureData, $key);
            return TRUE;
        } catch (Exception $e) {
            $lastException = $e;
        }


        /* We were unable to validate the signature with any of our keys. */
        if ($lastException !== NULL) {
            throw $lastException;
        } else {
            return FALSE;
        }

    }


    public static function validateIssuerAndAudience($samlResponse, $spEntityId, $issuerToValidateAgainst, $relayState) {
        $issuer = current($samlResponse->getAssertions())->getIssuer();
        $assertion = current($samlResponse->getAssertions());
        $audiences = $assertion->getValidAudiences();
        if(strcmp($issuerToValidateAgainst, $issuer) === 0) {
            if(!empty($audiences)) {
                if(in_array($spEntityId, $audiences, TRUE)) {
                    return TRUE;
                } else {
                    if($relayState=='testValidate'){
					$Error_message=mo_saml_options_error_constants::Error_invalid_audience;
					$Cause_message = mo_saml_options_error_constants::Cause_invalid_audience;
                    echo '<div style="font-family:Calibri;padding:0 3%;">';
                    echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ' . __('ERROR','miniorange-saml-20-single-sign-on') . '</div>
                    <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>' . __('Error','miniorange-saml-20-single-sign-on') . ': </strong>'.$Error_message.'</p>
                    
                    <p><strong>' . __('Possible Cause','miniorange-saml-20-single-sign-on'). ': </strong>'.$Cause_message.'</p>
                    <p>' . __('Expected one of the Audiences to be','miniorange-saml-20-single-sign-on'). ': '.$spEntityId.'<p>
                    </div>';
                    mo_saml_download_logs($Error_message,$Cause_message);
                    exit;
                }
                else
                {
                    wp_die(__("We could not sign you in. Please contact your administrator",'miniorange-saml-20-single-sign-on'),"Error: Invalid Audience URI");
                }
                }
            }
        } else {
            if($relayState=='testValidate'){

	            $Error_message=mo_saml_options_error_constants::Error_issuer_not_verfied;
	            $Cause_message = mo_saml_options_error_constants::Cause_issuer_not_verfied;
	            update_option('mo_saml_required_issuer',$issuer);
                echo '<div style="font-family:Calibri;padding:0 3%;">';
                echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;">' . __('ERROR','miniorange-saml-20-single-sign-on') . '</div>
                <div style="color: #a94442;font-size:14pt; margin-bottom:20px;text-align: justify"><p><strong>' . __('Error','miniorange-saml-20-single-sign-on'). ':'.$Error_message.' </strong></p>
                
                <p><strong>' . __('Possible Cause','miniorange-saml-20-single-sign-on') . ':'.$Cause_message.' </strong></p>
               <div>
			    <ol style="text-align: center">
                    <form method="post" action="" name="mo_fix_entityid" id="mo_fix_certificate">';
                    wp_nonce_field('mo_fix_entity_id');
				    echo '<input type="hidden" name="option" value="mo_fix_entity_id" />
				    <input type="submit" class="miniorange-button" style="width: 55%" value="' . __('Fix Issue','miniorange-saml-20-single-sign-on' ) .'">
				    </form>
                </ol> 
             </div>
                </div>
                </div>';

	            mo_saml_download_logs($Error_message,$Cause_message);
                 exit;
        }
         else
                {
                    wp_die(__("We could not sign you in. Please contact your administrator",'miniorange-saml-20-single-sign-on'),"Error: Issuer cannot be verified");
                }
    }
}

    private static function findCertificate(array $certFingerprints, array $certificates, $relayState) {

        $candidates = array();

        //foreach ($certificates as $cert) {
            $fp = strtolower(sha1(base64_decode($certificates[0])));
            if (!in_array($fp, $certFingerprints, TRUE)) {
                $candidates[] = $fp;
                return false;
                //continue;
            }

            /* We have found a matching fingerprint. */
            $pem = "-----BEGIN CERTIFICATE-----\n" .
                chunk_split($certificates[0], 64) .
                "-----END CERTIFICATE-----\n";

            return $pem;
      //  }

        // if($relayState=='testValidate'){
        //     $pem = "-----BEGIN CERTIFICATE-----<br>" .
        //         chunk_split($cert, 64) .
        //         "<br>-----END CERTIFICATE-----";

        //     echo '<div style="font-family:Calibri;padding:0 3%;">';
        //     echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
        //     <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>Unable to find a certificate matching the configured fingerprint.</p>
        //     <p>Please contact your administrator and report the following error:</p>
        //     <p><strong>Possible Cause: </strong>Content of \'X.509 Certificate\' field in Service Provider Settings is incorrect. Please replace it with certificate given below.</p>
        //     <p><strong>Certificate found in SAML Response: </strong><br><br>'.$pem.'</p>
        //         </div>
        //         <div style="margin:3%;display:block;text-align:center;">
        //         <form action="index.php">
        //         <div style="margin:3%;display:block;text-align:center;"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="self.close();"></div>';

        //         exit;
        //     }
        //     else{
        //         wp_die("We could not sign you in. Please contact your administrator","Error: Invalid Certificate");
        //     }
    }

        /**
     * Decrypt an encrypted element.
     *
     * This is an internal helper function.
     *
     * @param  DOMElement     $encryptedData The encrypted data.
     * @param  MoXMLSecurityKey $inputKey      The decryption key.
     * @param  array          &$blacklist    Blacklisted decryption algorithms.
     * @return DOMElement     The decrypted element.
     * @throws Exception
     */
    private static function doDecryptElement(DOMElement $encryptedData, MoXMLSecurityKey $inputKey, array &$blacklist)
    {
        $enc = new MoXMLSecEnc();
        $enc->setNode($encryptedData);

        $enc->type = $encryptedData->getAttribute("Type");
        $symmetricKey = $enc->locateKey($encryptedData);
        if (!$symmetricKey) {
            echo sprintf(__('Could not locate key algorithm in encrypted data.','miniorange-saml-20-single-sign-on'));
            exit;
        }

        $symmetricKeyInfo = $enc->locateKeyInfo($symmetricKey);
        if (!$symmetricKeyInfo) {
            echo sprintf(__('Could not locate <dsig:KeyInfo> for the encrypted key.','miniorange-saml-20-single-sign-on'));
            exit;
        }
        $inputKeyAlgo = $inputKey->getAlgorith();
        if ($symmetricKeyInfo->isEncrypted) {
            $symKeyInfoAlgo = $symmetricKeyInfo->getAlgorith();
            if (in_array($symKeyInfoAlgo, $blacklist, TRUE)) {
                echo sprintf('Algorithm disabled: ' . var_export($symKeyInfoAlgo, TRUE));
                exit;
            }
            if ($symKeyInfoAlgo === MoXMLSecurityKey::RSA_OAEP_MGF1P && $inputKeyAlgo === MoXMLSecurityKey::RSA_1_5) {
                /*
                 * The RSA key formats are equal, so loading an RSA_1_5 key
                 * into an RSA_OAEP_MGF1P key can be done without problems.
                 * We therefore pretend that the input key is an
                 * RSA_OAEP_MGF1P key.
                 */
                $inputKeyAlgo = MoXMLSecurityKey::RSA_OAEP_MGF1P;
            }
            /* Make sure that the input key format is the same as the one used to encrypt the key. */
            if ($inputKeyAlgo !== $symKeyInfoAlgo) {
                echo sprintf( 'Algorithm mismatch between input key and key used to encrypt ' .
                    ' the symmetric key for the message. Key was: ' .
                    var_export($inputKeyAlgo, TRUE) . '; message was: ' .
                    var_export($symKeyInfoAlgo, TRUE));
                exit;
            }
            /** @var MoXMLSecEnc $encKey */
            $encKey = $symmetricKeyInfo->encryptedCtx;
            $symmetricKeyInfo->key = $inputKey->key;
            $keySize = $symmetricKey->getSymmetricKeySize();
            if ($keySize === NULL) {
                /* To protect against "key oracle" attacks, we need to be able to create a
                 * symmetric key, and for that we need to know the key size.
                 */
                echo sprintf('Unknown key size for encryption algorithm: ' . var_export($symmetricKey->type, TRUE));
                exit;
            }
            try {
                $key = $encKey->decryptKey($symmetricKeyInfo);
                if (strlen($key) != $keySize) {
                    echo sprintf('Unexpected key size (' . strlen($key) * 8 . 'bits) for encryption algorithm: ' .
                        var_export($symmetricKey->type, TRUE));
                    exit;
                }
            } catch (Exception $e) {
                /* We failed to decrypt this key. Log it, and substitute a "random" key. */

                /* Create a replacement key, so that it looks like we fail in the same way as if the key was correctly padded. */
                /* We base the symmetric key on the encrypted key and private key, so that we always behave the
                 * same way for a given input key.
                 */
                $encryptedKey = $encKey->getCipherValue();
                $pkey = openssl_pkey_get_details($symmetricKeyInfo->key);
                $pkey = sha1(serialize($pkey), TRUE);
                $key = sha1($encryptedKey . $pkey, TRUE);
                /* Make sure that the key has the correct length. */
                if (strlen($key) > $keySize) {
                    $key = substr($key, 0, $keySize);
                } elseif (strlen($key) < $keySize) {
                    $key = str_pad($key, $keySize);
                }
            }
            $symmetricKey->loadkey($key);
        } else {
            $symKeyAlgo = $symmetricKey->getAlgorith();
            /* Make sure that the input key has the correct format. */
            if ($inputKeyAlgo !== $symKeyAlgo) {
                echo sprintf( 'Algorithm mismatch between input key and key in message. ' .
                    'Key was: ' . var_export($inputKeyAlgo, TRUE) . '; message was: ' .
                    var_export($symKeyAlgo, TRUE));
                exit;
            }
            $symmetricKey = $inputKey;
        }
        $algorithm = $symmetricKey->getAlgorith();
        if (in_array($algorithm, $blacklist, TRUE)) {
            echo sprintf('Algorithm disabled: ' . var_export($algorithm, TRUE));
            exit;
        }
        /** @var string $decrypted */
        $decrypted = $enc->decryptNode($symmetricKey, FALSE);
        /*
         * This is a workaround for the case where only a subset of the XML
         * tree was serialized for encryption. In that case, we may miss the
         * namespaces needed to parse the XML.
         */
        $xml = '<root xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" '.
                     'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
            $decrypted .
            '</root>';
        $newDoc = new DOMDocument();
        if (!@$newDoc->loadXML($xml)) {
            echo sprintf('Failed to parse decrypted XML. Maybe the wrong sharedkey was used?');
            throw new Exception('Failed to parse decrypted XML. Maybe the wrong sharedkey was used?');
        }
        $decryptedElement = $newDoc->firstChild->firstChild;
        if ($decryptedElement === NULL) {
            echo sprintf('Missing encrypted element.');
            throw new Exception('Missing encrypted element.');
        }

        if (!($decryptedElement instanceof DOMElement)) {
            echo sprintf('Decrypted element was not actually a DOMElement.');
        }

        return $decryptedElement;
    }
    /**
     * Decrypt an encrypted element.
     *
     * @param  DOMElement     $encryptedData The encrypted data.
     * @param  MoXMLSecurityKey $inputKey      The decryption key.
     * @param  array          $blacklist     Blacklisted decryption algorithms.
     * @return DOMElement     The decrypted element.
     * @throws Exception
     */
    public static function decryptElement(DOMElement $encryptedData, MoXMLSecurityKey $inputKey, array $blacklist = array(), MoXMLSecurityKey $alternateKey = NULL)
    {
        try {
            return self::doDecryptElement($encryptedData, $inputKey, $blacklist);
        } catch (Exception $e) {
            //Try with alternate key
            try {
                return self::doDecryptElement($encryptedData, $alternateKey, $blacklist);
            } catch(Exception $t) {

            }
            /*
             * Something went wrong during decryption, but for security
             * reasons we cannot tell the user what failed.
             */
            //print_r($e->getMessage());
            echo sprintf('Failed to decrypt XML element.');
            exit;
        }
    }

     /**
     * Generates the metadata of the SP based on the settings
     *
     * @param string    $sp            The SP data
     * @param string    $authnsign     authnRequestsSigned attribute
     * @param string    $wsign         wantAssertionsSigned attribute
     * @param DateTime  $validUntil    Metadata's valid time
     * @param Timestamp $cacheDuration Duration of the cache in seconds
     * @param array     $contacts      Contacts info
     * @param array     $organization  Organization ingo
     *
     * @return string SAML Metadata XML
     */
    public static function metadata_builder($siteUrl)
    {
        $xml = new DOMDocument();
        $url = plugins_url().'/miniorange-saml-20-single-sign-on/sp-metadata.xml';

        $xml->load($url);

        $xpath = new DOMXPath($xml);
        $elements = $xpath->query('//md:EntityDescriptor[@entityID="http://{path-to-your-site}/wp-content/plugins/miniorange-saml-20-single-sign-on/"]');

         if ($elements->length >= 1) {
            $element = $elements->item(0);
            $element->setAttribute('entityID', $siteUrl.'/wp-content/plugins/miniorange-saml-20-single-sign-on/');
        }

        $elements = $xpath->query('//md:AssertionConsumerService[@Location="http://{path-to-your-site}"]');
        if ($elements->length >= 1) {
            $element = $elements->item(0);
            $element->setAttribute('Location', $siteUrl.'/');
        }

        //re-save
        $xml->save(plugins_url()."/miniorange-saml-20-single-sign-on/sp-metadata.xml");
    }

    public static function get_mapped_groups($saml_params, $saml_groups)
    {
            $groups = array();

        if (!empty($saml_groups)) {
            $saml_mapped_groups = array();
            $i=1;
            while ($i < 10) {
                $saml_mapped_groups_value = $saml_params->get('group'.$i.'_map');

                $saml_mapped_groups[$i] = explode(';', $saml_mapped_groups_value);
                $i++;
            }
        }

        foreach ($saml_groups as $saml_group) {
            if (!empty($saml_group)) {
                $i = 0;
                $found = false;

                while ($i < 9 && !$found) {
                    if (!empty($saml_mapped_groups[$i]) && in_array($saml_group, $saml_mapped_groups[$i], TRUE)) {
                        $groups[] = $saml_params->get('group'.$i);
                        $found = true;
                    }
                    $i++;
                }
            }
        }

        return array_unique($groups);
    }


    public static function getEncryptionAlgorithm($method){
        switch($method){
            case 'http://www.w3.org/2001/04/xmlenc#tripledes-cbc':
                return MoXMLSecurityKey::TRIPLEDES_CBC;
                break;

            case 'http://www.w3.org/2001/04/xmlenc#aes128-cbc':
                return MoXMLSecurityKey::AES128_CBC;

            case 'http://www.w3.org/2001/04/xmlenc#aes192-cbc':
                return MoXMLSecurityKey::AES192_CBC;
                break;

            case 'http://www.w3.org/2001/04/xmlenc#aes256-cbc':
                return MoXMLSecurityKey::AES256_CBC;
                break;

            case 'http://www.w3.org/2001/04/xmlenc#rsa-1_5':
                return MoXMLSecurityKey::RSA_1_5;
                break;

            case 'http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p':
                return MoXMLSecurityKey::RSA_OAEP_MGF1P;
                break;

            case 'http://www.w3.org/2000/09/xmldsig#dsa-sha1':
                return MoXMLSecurityKey::DSA_SHA1;
                break;

            case 'http://www.w3.org/2000/09/xmldsig#rsa-sha1':
                return MoXMLSecurityKey::RSA_SHA1;
                break;

            case 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256':
                return MoXMLSecurityKey::RSA_SHA256;
                break;

            case 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha384':
                return MoXMLSecurityKey::RSA_SHA384;
                break;

            case 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha512':
                return MoXMLSecurityKey::RSA_SHA512;
                break;

            default:
                echo sprintf('Invalid Encryption Method: '. htmlspecialchars($method));
                exit;
                break;
        }
    }

    public static function sanitize_certificate( $certificate ) {
        $certificate = preg_replace("/[\r\n]+/", "", $certificate);
        $certificate = str_replace( "-", "", $certificate );
        $certificate = str_replace( "BEGIN CERTIFICATE", "", $certificate );
        $certificate = str_replace( "END CERTIFICATE", "", $certificate );
        $certificate = str_replace( " ", "", $certificate );
        $certificate = chunk_split($certificate, 64, "\r\n");
        $certificate = "-----BEGIN CERTIFICATE-----\r\n" . $certificate . "-----END CERTIFICATE-----";
        return $certificate;
    }

    public static function desanitize_certificate( $certificate ) {
        $certificate = preg_replace("/[\r\n]+/", "", $certificate);
        //$certificate = str_replace( "-", "", $certificate );
        $certificate = str_replace( "-----BEGIN CERTIFICATE-----", "", $certificate );
        $certificate = str_replace( "-----END CERTIFICATE-----", "", $certificate );
        $certificate = str_replace( " ", "", $certificate );
        //$certificate = chunk_split($certificate, 64, "\r\n");
        //$certificate = "-----BEGIN CERTIFICATE-----\r\n" . $certificate . "-----END CERTIFICATE-----";
        return $certificate;
    }

    public static function mo_saml_wp_remote_post($url, $args = array()){
        $response = wp_remote_post($url, $args);
        if(!is_wp_error($response)){
			return $response;
		} else {
            $show_message = new saml_mo_login();
            update_option('mo_saml_message', __('Unable to connect to the Internet. Please try again.','miniorange-saml-20-single-sign-on'));
            $show_message->mo_saml_show_error_message();
        }
    }
    
    public static function mo_saml_wp_remote_get($url, $args = array()){
		$response = wp_remote_get($url, $args);
		if(!is_wp_error($response)){
			return $response;
		} else {
            $show_message = new saml_mo_login();
            update_option('mo_saml_message', __('Unable to connect to the Internet. Please try again.','miniorange-saml-20-single-sign-on'));
            $show_message->mo_saml_show_error_message();
        }
    }
    
}
?>