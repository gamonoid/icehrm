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

include 'Assertion.php';

/**
 * Class for SAML2 Response messages.
 *
 */
class SAML2_Response
{
    /**
     * The assertions in this response.
     */
    private $assertions;
	
	/**
     * The destination URL in this response.
     */
	private $destination;
	
	private $certificates;
	private $signatureData;

    /**
     * Constructor for SAML 2 response messages.
     *
     * @param DOMElement|NULL $xml The input message.
     */
    public function __construct(DOMElement $xml = NULL)
    {
        //parent::__construct('Response', $xml);

        $this->assertions = array();
		$this->certificates = array();

        if ($xml === NULL) {
            return;
        }
		
		$sig = Utilities::validateElement($xml);
		if ($sig !== FALSE) {
			$this->certificates = $sig['Certificates'];
			$this->signatureData = $sig;
		}
		
		/* set the destination from saml response */
		if ($xml->hasAttribute('Destination')) {
            $this->destination = $xml->getAttribute('Destination');
        }
		
		for ($node = $xml->firstChild; $node !== NULL; $node = $node->nextSibling) {
			if ($node->namespaceURI !== 'urn:oasis:names:tc:SAML:2.0:assertion') {
				continue;
			}
			
			if ($node->localName === 'Assertion' || $node->localName === 'EncryptedAssertion') {
				$this->assertions[] = new SAML2_Assertion($node);
			}
			
		}
    }

    /**
     * Retrieve the assertions in this response.
     *
     * @return SAML2_Assertion[]|SAML2_EncryptedAssertion[]
     */
    public function getAssertions()
    {	
        return $this->assertions;
    }

    /**
     * Set the assertions that should be included in this response.
     *
     * @param SAML2_Assertion[]|SAML2_EncryptedAssertion[] The assertions.
     */
    public function setAssertions(array $assertions)
    {
        $this->assertions = $assertions;
    }
	
	public function getDestination()
    {
        return $this->destination;
    }


	public function getCertificates()
	{
		return $this->certificates;
	}

	public function getSignatureData()
	{
		return $this->signatureData;
	}
}