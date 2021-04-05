<?php

include_once 'Utilities.php';
class IDPMetadataReader{

	private $identityProviders;
	private $serviceProviders;

	public function __construct(DOMNode $xml = NULL){

        $this->identityProviders = array();
        $this->serviceProviders = array();

        $entitiesDescriptor = Utilities::xpQuery($xml, './saml_metadata:EntitiesDescriptor');

        if(!empty($entitiesDescriptor))
            $entityDescriptors = Utilities::xpQuery($entitiesDescriptor[0], './saml_metadata:EntityDescriptor');
        else
            $entityDescriptors = Utilities::xpQuery($xml, './saml_metadata:EntityDescriptor');

        foreach ($entityDescriptors as $entityDescriptor) {
            $idpSSODescriptor = Utilities::xpQuery($entityDescriptor, './saml_metadata:IDPSSODescriptor');

            if(isset($idpSSODescriptor) && !empty($idpSSODescriptor)){
                array_push($this->identityProviders,new IdentityProviders($entityDescriptor));
            }
            //TODO: add sp descriptor
        }
	}

	public function getIdentityProviders(){
		return $this->identityProviders;
	}

	public function getServiceProviders(){
		return $this->serviceProviders;
	}

}

class IdentityProviders{

	private $idpName;
	private $entityID;
	private $loginDetails;
	private $logoutDetails;
	private $signingCertificate;
	private $encryptionCertificate;
	private $signedRequest;

	public function __construct(DOMElement $xml = NULL){

		$this->idpName = '';
		$this->loginDetails = array();
		$this->logoutDetails = array();
		$this->signingCertificate = array();
		$this->encryptionCertificate = array();

		if ($xml->hasAttribute('entityID')) {
            $this->entityID = $xml->getAttribute('entityID');
        }

        if($xml->hasAttribute('WantAuthnRequestsSigned')){
        	$this->signedRequest = $xml->getAttribute('WantAuthnRequestsSigned');
        }

        $idpSSODescriptor = Utilities::xpQuery($xml, './saml_metadata:IDPSSODescriptor');

        if (count($idpSSODescriptor) > 1) {
            throw new Exception('More than one <IDPSSODescriptor> in <EntityDescriptor>.');
        } elseif (empty($idpSSODescriptor)) {
            throw new Exception('Missing required <IDPSSODescriptor> in <EntityDescriptor>.');
        }
        $idpSSODescriptorEL = $idpSSODescriptor[0];

        $info = Utilities::xpQuery($xml, './saml_metadata:Extensions');
        
        if($info)
        	$this->parseInfo($idpSSODescriptorEL);
        $this->parseSSOService($idpSSODescriptorEL);
        $this->parseSLOService($idpSSODescriptorEL);
        $this->parsex509Certificate($idpSSODescriptorEL);

	}

	private function parseInfo($xml){
		$displayNames = Utilities::xpQuery($xml, './mdui:UIInfo/mdui:DisplayName');
		foreach ($displayNames as $name) {
			if($name->hasAttribute('xml:lang') && $name->getAttribute('xml:lang')=="en"){
				$this->idpName = $name->textContent;
			}
		}
	}

	private function parseSSOService($xml){
		$ssoServices = Utilities::xpQuery($xml, './saml_metadata:SingleSignOnService');
		foreach ($ssoServices as $ssoService) {
			$binding = str_replace("urn:oasis:names:tc:SAML:2.0:bindings:","",$ssoService->getAttribute('Binding'));
	        $this->loginDetails = array_merge( 
	        	$this->loginDetails, 
	        	array($binding => $ssoService->getAttribute('Location')) 
	        );
	    }
	}

	private function parseSLOService($xml){
		$sloServices = Utilities::xpQuery($xml, './saml_metadata:SingleLogoutService');
		foreach ($sloServices as $sloService) {
			$binding = str_replace("urn:oasis:names:tc:SAML:2.0:bindings:","",$sloService->getAttribute('Binding'));
	        $this->logoutDetails = array_merge( 
	        	$this->logoutDetails, 
	        	array($binding => $sloService->getAttribute('Location')) 
	        );
	    }
	}

	private function parsex509Certificate($xml){
		foreach ( Utilities::xpQuery($xml, './saml_metadata:KeyDescriptor') as $KeyDescriptorNode ) {
			if($KeyDescriptorNode->hasAttribute('use')){
				if($KeyDescriptorNode->getAttribute('use')=='encryption'){
					$this->parseEncryptionCertificate($KeyDescriptorNode);
				}else{
					$this->parseSigningCertificate($KeyDescriptorNode);
				}
			}else{
				$this->parseSigningCertificate($KeyDescriptorNode);
			}
		}
	}

	private function parseSigningCertificate($xml){
		$certNode = Utilities::xpQuery($xml, './ds:KeyInfo/ds:X509Data/ds:X509Certificate');
		$certData = trim($certNode[0]->textContent);
		$certData = str_replace(array ( "\r", "\n", "\t", ' '), '', $certData);
		if(!empty($certNode))
			array_push($this->signingCertificate, Utilities::sanitize_certificate( $certData ));
	}


	private function parseEncryptionCertificate($xml){
		$certNode = Utilities::xpQuery($xml, './ds:KeyInfo/ds:X509Data/ds:X509Certificate');
		$certData = trim($certNode[0]->textContent);
		$certData = str_replace(array ( "\r", "\n", "\t", ' '), '', $certData);
		if(!empty($certNode))
			array_push($this->encryptionCertificate, $certData);
	}

	public function getIdpName(){
		return "";
	}

	public function getEntityID(){
		return $this->entityID;
	}

	public function getLoginURL($binding){
		return $this->loginDetails[$binding];
	}

	public function getLogoutURL($binding){
		return $this->logoutDetails[$binding];
	}

	public function getLoginDetails(){
		return $this->loginDetails;
	}

	public function getLogoutDetails(){
		return $this->logoutDetails;
	}

	public function getSigningCertificate(){
		return $this->signingCertificate;
	}

	public function getEncryptionCertificate(){
		return $this->encryptionCertificate[0];
	}

	public function isRequestSigned(){
		return $this->signedRequest;
	}

}

