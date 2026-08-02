<?php

namespace Classes;

use \RobRichards\XMLSecLibs\MoXMLSecurityKey;
use Utils\LogManager;

class SAMLManager
{
    public function getSSOEmail($samlData, $relayState)
    {
        // Service Providers Assertion Consumer Service (ACS) URL
        $acsUrl = CLIENT_BASE_URL.'login.php';
        $samlResponse = htmlspecialchars($samlData);

        $samlResponse = base64_decode($samlResponse);

        // XXE hardening: PHP 7.3 (the production target) loads external entities by default.
        // LIBXML_NOENT is deliberately NOT passed — it *enables* entity substitution, which is
        // the primitive an XXE payload needs.
        if (PHP_VERSION_ID < 80000) {
            $previousEntityLoader = libxml_disable_entity_loader(true);
        }
        $document = new \DOMDocument();
        $loaded = $document->loadXML($samlResponse, LIBXML_NONET);
        if (PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader($previousEntityLoader);
        }
        if (!$loaded) {
            LogManager::getInstance()->error('SAML login failed: response is not well-formed XML');

            return false;
        }
        $samlResponseXml = $document->firstChild;

        $doc = $document->documentElement;
        $xpath = new \DOMXpath($document);
        $xpath->registerNamespace('samlp', 'urn:oasis:names:tc:SAML:2.0:protocol');
        $xpath->registerNamespace('saml', 'urn:oasis:names:tc:SAML:2.0:assertion');

        $status = $xpath->query('/samlp:Response/samlp:Status/samlp:StatusCode', $doc);
        $statusString = $status->item(0)->getAttribute('Value');


        $statusArray = explode(':', $statusString);
        if (array_key_exists(7, $statusArray)) {
            $status = $statusArray[7];
        }

        if ('Success' !== $status) {
            $StatusMessage = $xpath->query('/samlp:Response/samlp:Status/samlp:StatusMessage', $doc)->item(0);
            LogManager::getInstance()->error('SAML login failed: status = '. $status);
            if (!empty($StatusMessage)) {
                $StatusMessage = $StatusMessage->nodeValue;
                LogManager::getInstance()->error('SAML login failed: status message = '. $StatusMessage);
            }
            return false;
        }

        $x509cert = SettingsManager::getInstance()->getSetting('SAML: X.509 Certificate');

        $samlResponse = new \SAML2_Response($samlResponseXml);
        $responseSignatureData = $samlResponse->getSignatureData();
        $assertionSignatureData = current($samlResponse->getAssertions())->getSignatureData();

        $certFingerPrint = MoXMLSecurityKey::getRawThumbprint($x509cert);
        $certFingerPrint = preg_replace('/\s+/', '', $certFingerPrint);
        $validSignature = false;
        if (!empty($responseSignatureData)) {
            $validSignature = \Utilities::processResponse(
                $acsUrl,
                $certFingerPrint,
                $responseSignatureData,
                $samlResponse,
                0,
                $relayState
            );
            LogManager::getInstance()->error('SAML: response signature validity :'.$validSignature);
        }

        if (!empty($assertionSignatureData)) {
            $validSignature = \Utilities::processResponse(
                $acsUrl,
                $certFingerPrint,
                $assertionSignatureData,
                $samlResponse,
                0,
                $relayState
            );
            LogManager::getInstance()->error('SAML: response signature validity :'.$validSignature);
        }

        if (!$validSignature) {
            LogManager::getInstance()->error('Invalid response or assertion signature');
            return false;
        }

        $issuer = current($samlResponse->getAssertions())->getIssuer();
        $assertion = current($samlResponse->getAssertions());
        $audiences = $assertion->getValidAudiences();
        $expectedIssuer = SettingsManager::getInstance()->getSetting('SAML: IDP Issuer');
        if ($issuer !== $expectedIssuer) {
            LogManager::getInstance()->error('SAML Invalid Issuer :'.$issuer.' expected :'.$expectedIssuer);
            return false;
        }

        $ssoEmail = current(current($samlResponse->getAssertions())->getNameId());
        if (!$ssoEmail) {
            return false;
        }

        return  $ssoEmail;
    }
}
